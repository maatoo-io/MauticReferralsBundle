<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\SubmissionEvent;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\FormBundle\FormEvents;
use Mautic\FormBundle\Model\FieldModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticReferralsBundle\Entity\Referral;
use MauticPlugin\MauticReferralsBundle\Form\Type\ReferralsType;
use MauticPlugin\MauticReferralsBundle\Integration\ReferralsIntegration;
use MauticPlugin\MauticReferralsBundle\Model\ReferralModel;
use MauticPlugin\MauticReferralsBundle\ReferralsEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormSubscriber implements EventSubscriberInterface
{
    const MODEL_NAME_KEY_LEAD = 'lead.lead';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var FieldModel
     */
    private $fieldModel;

    /**
     * @var ReferralModel
     */
    private $referralModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IntegrationHelper $integrationHelper,
        LeadModel $leadModel,
        FieldModel $fieldModel,
        ReferralModel $referralModel,
        TranslatorInterface $translator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $integrationObject     = $integrationHelper->getIntegrationObject(ReferralsIntegration::INTEGRATION_NAME);

        if ($integrationObject instanceof AbstractIntegration) {
            $keys            = $integrationObject->getKeys();
            //$this->siteKey   = isset($keys['site_key']) ? $keys['site_key'] : null;
            //$this->secretKey = isset($keys['secret_key']) ? $keys['secret_key'] : null;
        }
        $this->leadModel     = $leadModel;
        $this->fieldModel    = $fieldModel;
        $this->referralModel = $referralModel;
        $this->translator    = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD         => ['onFormBuild', 0],
            FormEvents::FORM_ON_SUBMIT        => ['onFormSubmit', 0],
            ReferralsEvents::ON_FORM_VALIDATE => ['onFormValidate', 0],
        ];
    }

    /**
     * @throws \Mautic\CoreBundle\Exception\BadConfigurationException
     */
    public function onFormBuild(FormBuilderEvent $event)
    {
        /*if (!$this->referralsIsConfigured) {
            return;
        }*/

        $event->addFormField('plugin.referrals', [
            'label'          => 'mautic.plugin.referrals',
            'formType'       => ReferralsType::class,
            'template'       => 'MauticReferralsBundle:Integration:referrals.html.php',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => true,
                'addDefaultValue'  => false,
                'addSaveResult'    => true,
            ],
            'site_key' => $this->siteKey,
        ]);

        $event->addValidator('plugin.referrals.validator', [
            'eventName' => ReferralsEvents::ON_FORM_VALIDATE,
            'fieldType' => 'plugin.referrals',
        ]);
    }

    /**
     * @throws \Mautic\CoreBundle\Exception\BadConfigurationException
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        //$form    = $event->getSubmission()->getForm();
        $fields   = $event->getFields();
        $post     = $event->getPost();
        $results  = $event->getResults();
        $referrer = $event->getLead();

        foreach ($fields as $field) {
            if ('plugin.referrals' === $field['type']) {
                $fieldEntity = $this->fieldModel->getEntity($field['id']);
                $props       = $fieldEntity->getProperties();

                $valid_referrals = [];
                if (!empty($post[$field['alias']]) && is_array($post[$field['alias']])) {
                    foreach ($post[$field['alias']] as $referral_email) {
                        if ('' !== $referral_email) {
                            $valid_referrals[] = $referral_email;
                            $lead              = (new Lead())
                                ->addUpdatedField('email', $referral_email);
                            //->addUpdatedField('firstname', $info['firstname'])
                            //->addUpdatedField('lastname', $info['lastname']);

                            $this->leadModel->saveEntity($lead);
                            if (!empty($props['add_tags']) && is_array($props['add_tags'])) {
                                $this->leadModel->setTags($lead, $props['add_tags']);
                            }

                            $referral = new Referral();
                            $referral->setLead($lead);
                            $referral->setReferrer($referrer);
                            $this->referralModel->saveEntity($referral);
                        }
                    }
                }
                $results[$field['alias']] = implode(', ', $valid_referrals);
            }
        }

        // TODO: Clean result (no empty values) -> doesn't work at the moment
        $event->setResults($results);

        return true;
    }

    public function onFormValidate(ValidationEvent $event)
    {
        $field = $event->getField();
        $value = $event->getValue();

        // TODO: Validate referral email addresses
        if (true === true) {
            return true;
        }

        $event->failedValidation(null === $this->translator ? 'Failed adding referrals.' : $this->translator->trans('mautic.integration.referrals.failure_message'));

        $this->eventDispatcher->addListener(LeadEvents::LEAD_POST_SAVE, function (LeadEvent $event) {
            if ($event->isNew()) {
                $this->leadModel->deleteEntity($event->getLead());
            }
        }, -255);
    }
}

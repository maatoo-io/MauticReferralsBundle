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
use Mautic\EmailBundle\Helper\EmailValidator;
use Mautic\EmailBundle\Exception\InvalidEmailException;
use MauticPlugin\MauticReferralsBundle\Entity\Referral;
use MauticPlugin\MauticReferralsBundle\Form\Type\ReferralsType;
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

    /**
     * @var EmailValidator
     */
    private $validator;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        LeadModel $leadModel,
        FieldModel $fieldModel,
        ReferralModel $referralModel,
        TranslatorInterface $translator,
        EmailValidator $validator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->leadModel     = $leadModel;
        $this->fieldModel    = $fieldModel;
        $this->referralModel = $referralModel;
        $this->translator    = $translator;
        $this->validator     = $validator;
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
        $event->addFormField('plugin.referrals', [
            'label'          => 'mautic.plugin.referrals',
            'formType'       => ReferralsType::class,
            'template'       => 'MauticReferralsBundle:Integration:referrals.html.php',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => true,
                'addDefaultValue'  => false,
                'addSaveResult'    => true,
                'addInputAttributes' => true,
            ],
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
        $fields   = $event->getFields();
        $post     = $event->getPost();
        $referrer = $event->getLead();

        foreach ($fields as $field) {
            if ('plugin.referrals' === $field['type']) {
                $fieldEntity = $this->fieldModel->getEntity($field['id']);
                $props       = $fieldEntity->getProperties();

                $valid_referrals = [];
                if (!empty($post[$field['alias']]) && is_array($post[$field['alias']])) {
                    foreach ($post[$field['alias']] as $referral_email) {
                        if ('' !== $referral_email) {
                            $leads = $this->leadModel->getRepository()->getLeadsByFieldValue('email', [$referral_email], null, true);

                            if (!isset($leads[strtolower($referral_email)])) {
                                $lead = (new Lead())
                                    ->addUpdatedField('email', $referral_email);
                                $this->leadModel->saveEntity($lead);
            
                                $leads[strtolower($referral_email)] = $lead;
                            } else {
                                $lead = $leads[strtolower($referral_email)];
                            }

                            $valid_referrals[] = $referral_email;

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
            }
        }

        return true;
    }

    public function onFormValidate(ValidationEvent $event)
    {
        $field = $event->getField();
        $value = $event->getValue();

        $validated = true;
        $reason = 'mautic.form.field.generic.required';
        if ('plugin.referrals' === $field->getType()) {
            if (is_array($value)) {
                $value = array_filter($value);
                if (count($value) > 0 ) {
                    foreach($value as $referral_email) {
                        if ('' !== $referral_email) {
                            try {
                                $this->validator->validate($referral_email);
                            } catch (InvalidEmailException $exception) {
                                $validated = false;
                                $reason = 'mautic.form.submission.email.invalid';
                            }
                        } 
                    }
                } else {
                    if ($field->getIsRequired()) {
                        $validated = false;
                    }
                }
            } else {
                $validated = false;
            }
        }

        if (!$validated) {
            $event->failedValidation($this->translator->trans($reason, [], 'validators'));
            $this->eventDispatcher->addListener(LeadEvents::LEAD_POST_SAVE, function (LeadEvent $event) {
                if ($event->isNew()) {
                    $this->leadModel->deleteEntity($event->getLead());
                }
            }, -255);
        }

    }

}

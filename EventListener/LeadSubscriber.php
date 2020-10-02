<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\EventListener;

use MauticPlugin\MauticReferralsBundle\Model\ReferralModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\CoreBundle\EventListener\ChannelTrait;
use Mautic\LeadBundle\Event\LeadChangeEvent;
use Mautic\LeadBundle\Event\LeadMergeEvent;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\ChannelTimelineInterface;
use Mautic\PageBundle\Model\EventModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    use ChannelTrait;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var ReferralModel
     */
    private $referralModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * LeadSubscriber constructor.
     *
     * @param LeadModel  $leadModel
     * @param ReferralModel $referralModel
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     */
    public function __construct(
        LeadModel $leadModel,
        ReferralModel $referralModel,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->leadModel      = $leadModel;
        $this->referralModel = $referralModel;
        $this->translator     = $translator;
        $this->router         = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => [
                ['onTimelineGenerate', 0],
            ],
        ];
    }

    /**
     * Compile referrals for the lead timeline.
     *
     * @param LeadTimelineEvent $event
     */
    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        
        $eventTypeKey  = 'plugin.referral';
        $eventTypeName = $this->translator->trans('mautic.referrals.timeline.event');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('pageList', 'eventDetails');

        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }
        
        $referrals = $this->referralModel->getRepository()->getLeadReferrals($event->getLeadId());
        foreach ($referrals as $referral) {
            $event->addEvent(
                [
                    'event'      => $eventTypeKey,
                    'eventId'    => $eventTypeKey.$referral['id'],
                    'eventLabel' => [
                        'label' => $this->translator->trans('mautic.referrals.timeline.event.referral', ['%id%' => $referral['lead_id']]),
                        'href'  => $this->router->generate(
                            'mautic_contact_action',
                            ['objectAction' => 'view', 'objectId' => $referral['lead_id']]
                        ),
                    ],
                    'eventType'  => $eventTypeName,
                    'timestamp'  => $referral['date_added'],
                    'extra'      => [
                        'event' => $referral,
                    ],
                    'contactId'       => 1,
                    'icon'            => 'fa-user-plus',
                ]
            );
        }

        $referrer = $this->referralModel->getRepository()->getLeadReferrer($event->getLeadId());
        if (count($referrer) == 1) {
            $referrer = $referrer[0];
            $event->addEvent(
                [
                    'event'      => $eventTypeKey,
                    'eventId'    => $eventTypeKey.$referrer['id'],
                    'eventLabel' => [
                        'label' => $this->translator->trans('mautic.referrals.timeline.event.referrer', ['%id%' => $referrer['referrer_id']]),
                        'href'  => $this->router->generate(
                            'mautic_contact_action',
                            ['objectAction' => 'view', 'objectId' => $referrer['referrer_id']]
                        ),
                    ],
                    'eventType'  => $eventTypeName,
                    'timestamp'  => $referrer['date_added'],
                    'extra'      => [
                        'event' => $referrer,
                    ],
                    'contactId'       => 1,
                    'icon'            => 'fa-user-plus',
                ]
            );
        }
    }

}

<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\EventListener;

use Mautic\CoreBundle\Helper\BuilderTokenHelperFactory;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\MauticReferralsBundle\Helper\TokenHelper;
use MauticPlugin\MauticReferralsBundle\Model\ReferralModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private static $contactFieldRegex = '{referrerfield=(.*?)}';

    /**
     * @var string
     */
    private $builderTokenHelperFactory;

    /**
     * @var string
     */
    private $referralModel;

    public function __construct(
        BuilderTokenHelperFactory $builderTokenHelperFactory,
        ReferralModel $referralModel
        ) {
        $this->builderTokenHelperFactory = $builderTokenHelperFactory;
        $this->referralModel             = $referralModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD   => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }

    public function onEmailBuild(EmailBuilderEvent $event)
    {
        $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('lead.field', 'lead:fields', 'MauticLeadBundle');
        // the permissions are for viewing contact data, not for managing contact fields
        $tokenHelper->setPermissionSet(['lead:leads:viewown', 'lead:leads:viewother']);

        if ($event->tokensRequested(self::$contactFieldRegex)) {
            $event->addTokensFromHelper($tokenHelper, self::$contactFieldRegex, 'label', 'alias');
        }
    }

    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    public function onEmailGenerate(EmailSendEvent $event)
    {
        // Combine all possible content to find tokens across them
        $content = $event->getSubject();
        $content .= $event->getContent();
        $content .= $event->getPlainText();
        $content .= implode(' ', $event->getTextHeaders());

        $lead = $event->getLead();

        if ($lead['id']) {
            $referrer = $this->referralModel->getRepository()->getLeadReferrer($lead['id']);
            if(count($referrer)) {
                $referrer = $referrer[0];
            } else {
                return;
            }
        } else {
            // Preview
            $referrer = $lead;
        }

        $tokenList = TokenHelper::findReferrerTokens($content, $referrer);
        if (count($tokenList)) {
            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }
}

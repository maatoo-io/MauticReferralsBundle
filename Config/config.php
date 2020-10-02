<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Referrals',
    'description' => 'Enables Referrals integration.',
    'version'     => '1.0',
    'author'      => 'maatoo.io',

    'routes' => [
    ],

    'services' => [
        'events' => [
            'mautic.referrals.event_listener.form_subscriber' => [
                'class'     => \MauticPlugin\MauticReferralsBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.lead.model.lead',
                    'mautic.form.model.field',
                    'mautic.referrals.model.referral',
                    'translator',
                    'mautic.validator.email',
                ],
            ],
            'mautic.referrals.event_listener.email_subscriber' => [
                'class'     => \MauticPlugin\MauticReferralsBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'mautic.helper.token_builder.factory',
                    'mautic.referrals.model.referral',
                ],
            ],
        ],
        'models' => [
            'mautic.referrals.model.referral' => [
                'class'     => \MauticPlugin\MauticReferralsBundle\Model\ReferralModel::class,
                'arguments' => [],
            ],
        ],
        'repositories' => [
            'mautic.referrals.repository.order_line' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \MauticPlugin\MauticReferralsBundle\Entity\Referral::class,
                ],
            ],
        ],
        'forms'=> [
            'mautic.referrals.type.referrals' => [
                'class'     => \MauticPlugin\MauticReferralsBundle\Form\Type\ReferralsType::class,
                'arguments' => ['translator'],
            ],
        ],
        'others'=> [
            'mautic.referrals.helper.token'                => [
                'class'     => \MauticPlugin\MauticReferralsBundle\Helper\TokenHelper::class,
            ],
        ],
        'integrations' => [
            'mautic.integration.referrals' => [
                'class'     => \MauticPlugin\MauticReferralsBundle\Integration\ReferralsIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
    'parameters' => [
    ],
];

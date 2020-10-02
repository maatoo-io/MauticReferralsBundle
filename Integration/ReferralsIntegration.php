<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class ReferralsIntegration.
 */
class ReferralsIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'Referrals';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'Referrals';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
            
        ];
    }
}

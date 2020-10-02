<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\MauticReferralsBundle\Entity\Referral;

/**
 * Class ReferralModel.
 */
class ReferralModel extends FormModel
{
    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\MauticReferralsBundle\Entity\ReferralRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticReferralsBundle:Referral');
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return OrderLine|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new Referral();
        }

        return parent::getEntity($id);
    }
}

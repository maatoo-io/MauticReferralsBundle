<?php

namespace MauticPlugin\MauticReferralsBundle\Entity;

use Doctrine\ORM\NoResultException;
use Mautic\CoreBundle\Entity\CommonRepository;

class ReferralRepository extends CommonRepository
{
    /**
     * Get a lead's referrer.
     *
     * @param int $leadId
     *
     * @return array
     */
    public function getLeadReferrer($leadId = null, array $options = [])
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('*')
            ->from(MAUTIC_TABLE_PREFIX.'referrals', 'r')
            ->leftJoin('r', MAUTIC_TABLE_PREFIX.'leads', 'l', 'r.referrer_id = l.id');

        if ($leadId) {
            $query->where('r.lead_id = '.(int) $leadId);
        }

        try {
            return $query->execute()->fetchAll()[0];
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'r';
    }
}

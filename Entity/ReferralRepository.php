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

        return $query->execute()->fetchAll();
    }

    /**
     * Get a lead's referrals.
     *
     * @param int $leadId
     *
     * @return array
     */
    public function getLeadReferrals($leadId)
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query->select('*')
            ->from(MAUTIC_TABLE_PREFIX.'referrals', 'r')
            ->where('r.referrer_id = '.(int) $leadId);

        return $query->execute()->fetchAll();
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'r';
    }
}

<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Referral
{
    /**
     * @var int
     */
    private $id;

    /*
     * @var Lead
     */
    private $lead;

    /*
     * @var Lead
     */
    private $referrer;

    /**
     * @var \DateTiem
     */
    protected $dateAdded;

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('referrals')
            ->setCustomRepositoryClass('MauticPlugin\MauticReferralsBundle\Entity\ReferralRepository');

        $builder->addId();

        $builder->createManyToOne('lead', Lead::class)
            ->addJoinColumn('lead_id', 'id', false, false, 'CASCADE')
            ->fetchLazy()
            ->build();

        $builder->createManyToOne('referrer', Lead::class)
            ->addJoinColumn('referrer_id', 'id', false, false, 'CASCADE')
            ->fetchLazy()
            ->build();

        $builder->addDateAdded();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    public function setLead(Lead $lead): void
    {
        $this->lead = $lead;
    }

    /**
     * @return Lead
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    public function setReferrer(Lead $referrer): void
    {
        $this->referrer = $referrer;
    }

    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param mixed $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }
}

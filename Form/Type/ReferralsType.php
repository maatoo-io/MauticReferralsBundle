<?php

/*
 * @copyright   2020 maatoo.io. All rights reserved
 * @author      maatoo.io
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticReferralsBundle\Form\Type;

use Mautic\LeadBundle\Form\Type\TagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ReferralsType.
 */
class ReferralsType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('referrals', IntegerType::class, [
            'label'      => 'mautic.referrals.count',
            'label_attr' => ['class' => 'control-label'],
            'required'   => true,
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mautic.referrals.count.descr',
            ],
            'data'            => (isset($options['data']['referrals'])) ? $options['data']['referrals'] : 3,
        ]);

        $builder->add(
            'add_tags',
            TagType::class,
            [
                'label' => 'mautic.lead.tags.add',
                'attr'  => [
                    'data-placeholder'     => $this->translator->trans('mautic.lead.tags.select_or_create'),
                    'data-no-results-text' => $this->translator->trans('mautic.lead.tags.enter_to_create'),
                    'data-allow-add'       => 'true',
                    'onchange'             => 'Mautic.createLeadTag(this)',
                ],
                'data'            => (isset($options['data']['add_tags'])) ? $options['data']['add_tags'] : null,
                'add_transformer' => true,
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'referrals';
    }
}

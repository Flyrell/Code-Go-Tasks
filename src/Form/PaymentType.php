<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Form;

use App\Entity\Payment;
use App\Transformer\SkillSelectToSkillTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    /**
     * @var SkillSelectToSkillTransformer
     */
    private $skillSelectToSkillTransformer;

    public function __construct(SkillSelectToSkillTransformer $skillSelectToSkillTransformer)
    {
        $this->skillSelectToSkillTransformer = $skillSelectToSkillTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('month', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker view-mode-months'],
            ])
            ->add('primarySkill', SelectSkillType::class, [
                'name_label' => 'Primary skill',
                'level_label' => 'Primary skill level',
            ])
            ->add('amount')
        ;

        $builder->get('primarySkill')->addModelTransformer($this->skillSelectToSkillTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Form;

use App\Entity\Employee;
use App\Form\Model\SkillFormModel;
use App\Transformer\SkillSelectsToSkillsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    /**
     * @var SkillSelectsToSkillsTransformer
     */
    private $skillSelectsToSkillsTransformer;

    public function __construct(SkillSelectsToSkillsTransformer $skillSelectsToSkillsTransformer)
    {
        $this->skillSelectsToSkillsTransformer = $skillSelectsToSkillsTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('email', EmailType::class)
            ->add('phone')
            ->add('skills', CollectionType::class, [
                'entry_type' => SelectSkillType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('addresses', CollectionType::class, [
                'entry_type' => AddressType::class,
                'entry_options' => ['label' => false],
                'label_attr' => ['class' => 'bold'],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('payments', CollectionType::class, [
                'entry_type' => PaymentType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);

        $builder->get('skills')->addModelTransformer($this->skillSelectsToSkillsTransformer);

        $builder->get('skills')->addEventListener(FormEvents::SUBMIT, [$this, 'validateUniqueSkillTypes']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function validateUniqueSkillTypes(FormEvent $event)
    {
        $form = $event->getForm();

        $skillUseCount = [];

        foreach ($form as $skillSelectForm) {
            /** @var FormInterface $skillSelectForm */
            /** @var SkillFormModel $skillSelectModel */
            $skillSelectModel = $skillSelectForm->getData();
            $skillName = $skillSelectModel->getName();
            if (array_key_exists($skillName, $skillUseCount)) {
                ++$skillUseCount[$skillName];
            } else {
                $skillUseCount[$skillName] = 1;
            }
        }

        foreach ($form as $skillSelectForm) {
            /** @var FormInterface $skillSelectForm */
            /** @var SkillFormModel $skillSelectModel */
            $skillSelectModel = $skillSelectForm->getData();
            if ($skillUseCount[$skillSelectModel->getName()] > 1) {
                $skillSelectForm->get('name')->addError(new FormError('Employee cannot have multiple skills with same name.'));
            }
        }
    }
}

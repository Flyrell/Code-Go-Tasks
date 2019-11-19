<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Form;

use App\Enum\SkillLevel;
use App\Repository\SkillRepository;
use App\Form\Model\SkillFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectSkillType extends AbstractType
{
    /**
     * @var SkillRepository
     */
    private $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $skillNames = [];
        foreach ($this->skillRepository->getSkillNames() as $skillName) {
            $skillNames[$skillName] = $skillName;
        }

        $builder->add('name', ChoiceType::class, [
            'placeholder' => $options['name_placeholder'],
            'label' => $options['name_label'],
            'choices' => $skillNames,
        ]);

        $builder->add('level', ChoiceType::class, [
            'placeholder' => $options['level_placeholder'],
            'label' => $options['level_label'],
            'choices' => SkillLevel::getChoices(),
        ]);

        $builder->get('name')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'validateNameBeforeDataTransforms']);
        $builder->get('level')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'validateLevelBeforeDataTransforms']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SkillFormModel::class,
            'name_label' => 'Skill',
            'name_placeholder' => 'Choose a skill',
            'level_label' => 'Level',
            'level_placeholder' => 'Choose a skill level',
        ]);
    }

    /**
     * Prevent skill not being chosen on submit.
     *
     * @param FormEvent $event
     */
    public function validateNameBeforeDataTransforms(FormEvent $event)
    {
        if (empty($event->getData())) {
            $event->getForm()->addError(new FormError('Please choose a skill'));
        }
    }

    /**
     * Prevent skill level not being chosen on submit.
     *
     * @param FormEvent $event
     */
    public function validateLevelBeforeDataTransforms(FormEvent $event)
    {
        if (empty($event->getData())) {
            $event->getForm()->addError(new FormError('Please choose a skill level'));
        }
    }
}

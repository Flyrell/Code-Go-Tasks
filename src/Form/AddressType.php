<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Form;

use App\Entity\Address;
use App\Form\EventSubscriber\StripWhitespaceListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /**
     * @var StripWhitespaceListener
     */
    private $stripWhitespaceListener;

    public function __construct(StripWhitespaceListener $stripWhitespaceListener)
    {
        $this->stripWhitespaceListener = $stripWhitespaceListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city')
            ->add('street')
            ->add('postalCode')
            ->add('country')
        ;

        $builder->get('postalCode')->addEventSubscriber($this->stripWhitespaceListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}








































































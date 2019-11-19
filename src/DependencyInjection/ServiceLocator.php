<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator as SymfonyServiceLocator;

class ServiceLocator extends SymfonyServiceLocator
{
    /**
     * @var array
     */
    private $serviceNames;

    /**
     * @param callable[] $factories
     */
    public function __construct(array $factories)
    {
        parent::__construct($factories);
        $this->serviceNames = array_keys($factories);
    }

    /**
     * @return array
     */
    public function getServiceNames(): array
    {
        return $this->serviceNames;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $services = [];
        foreach ($this->serviceNames as $serviceName) {
            $services[] = $this->get($serviceName);
        }

        return $services;
    }
}

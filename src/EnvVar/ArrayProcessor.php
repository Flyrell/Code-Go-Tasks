<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\EnvVar;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class ArrayProcessor implements EnvVarProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        return array_filter(array_map('trim', explode(',', $getEnv($name))));
    }

    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes()
    {
        return ['array' => 'array'];
    }
}

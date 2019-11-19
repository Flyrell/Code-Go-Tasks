<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Normalizer;

abstract class AbstractNormalizer
{
    /**
     * @param string $className
     *
     * @return string
     */
    protected function resolveDocumentClassName(string $className): string
    {
        $className = substr($className, strrpos($className, '\\') + 1);
        $className = $this->getDocumentClassNameNamespace().'\\'.$className;

        return $className;
    }

    /**
     * @return string
     */
    protected function getDocumentClassNameNamespace(): string
    {
        return 'App\\Document';
    }
}

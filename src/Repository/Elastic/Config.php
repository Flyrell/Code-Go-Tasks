<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Repository\Elastic;

class Config
{
    /**
     * @var string
     */
    private $indexPrefix;

    /**
     * @param string $indexPrefix
     */
    public function __construct(string $indexPrefix)
    {
        $this->indexPrefix = $indexPrefix;
    }

    /**
     * @return string
     */
    public function getIndexPrefix(): string
    {
        return $this->indexPrefix;
    }
}

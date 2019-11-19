<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Repository\Elastic;

use Symfony\Component\Serializer\Annotation\Groups;

class QueryResult
{
    /**
     * @var array
     */
    private $documents;

    /**
     * @var int
     */
    private $total;

    /**
     * @param array $documents
     * @param int   $total
     */
    public function __construct(array $documents, int $total)
    {
        $this->documents = $documents;
        $this->total = $total;
    }

    /**
     * @Groups({"api"})
     *
     * @return array
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @Groups({"api"})
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}

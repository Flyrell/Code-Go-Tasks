<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Document;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractDocument implements DocumentInterface
{
    /**
     * @var int
     * @Assert\NotNull
     */
    private $id;

    /**
     * @Groups({"storage", "api"})
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}

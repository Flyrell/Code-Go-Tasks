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

class SkillDocument extends AbstractDocument
{
    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var int
     * @Assert\Range(min=1, max=5)
     */
    private $level;

    //region # Getters and setters

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    //endregion # Getters and setters
}

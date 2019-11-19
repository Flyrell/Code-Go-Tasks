<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Consumer\Message;

class RecalculateSkillStatsMessage
{
    /**
     * @var int
     */
    private $skillId;

    /**
     * @var string|null
     */
    private $sender;

    public function __construct(int $skillId, ?string $sender)
    {
        $this->skillId = $skillId;
        $this->sender = $sender;
    }

    /**
     * @return int
     */
    public function getSkillId(): int
    {
        return $this->skillId;
    }

    /**
     * @param int $skillId
     */
    public function setSkillId(int $skillId): void
    {
        $this->skillId = $skillId;
    }

    /**
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * @param string|null $sender
     */
    public function setSender(?string $sender): void
    {
        $this->sender = $sender;
    }
}

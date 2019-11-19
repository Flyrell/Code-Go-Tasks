<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Document;

use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentDocument extends AbstractDocument
{
    /**
     * @var DateTime
     * @Assert\NotBlank
     */
    private $month;

    /**
     * @var float
     * @Assert\Range(min=0.01)
     * @Assert\NotBlank
     */
    private $amount;

    //region # Getters and setters

    /**
     * @Groups({"storage", "api"})
     *
     * @return DateTime
     */
    public function getMonth(): DateTime
    {
        return $this->month;
    }

    /**
     * @param DateTime $month
     */
    public function setMonth(DateTime $month): void
    {
        $this->month = $month;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    //endregion # Getters and setters
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message = "Please fill in month of the payment.")
     */
    private $month;

    /**
     * @ORM\Column(type="float")
     * @Assert\Range(min=0.01)
     * @Assert\NotBlank(message = "Please fill in the payment amount.")
     */
    private $amount;

    /**
     * @ManyToOne(targetEntity="Employee", inversedBy="payments")
     * @JoinColumn(name="employee_id", referencedColumnName="id")
     */
    private $employee;

    /**
     * @ManyToOne(targetEntity="Skill")
     * @JoinColumn(name="primary_skill_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $primarySkill;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonth(): ?DateTimeInterface
    {
        return $this->month;
    }

    public function setMonth(DateTimeInterface $month): void
    {
        $this->month = $month;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee = null): void
    {
        $this->employee = $employee;
    }

    public function getPrimarySkill(): ?Skill
    {
        return $this->primarySkill;
    }

    public function setPrimarySkill(Skill $primarySkill = null): void
    {
        $this->primarySkill = $primarySkill;
    }
}

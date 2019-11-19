<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeRepository")
 */
class Employee
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\Length(min=1, max=50)
     * @Assert\NotBlank
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\Length(min=1, max=50)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\Regex("/^\(?\+?[0-9]{1,3}\)? ?-?[0-9]{1,3} ?-?[0-9]{3,5} ?-?[0-9]{3,4}( ?-?[0-9]{3})? ?(\w{1,10}\s?\d{1,6})?/", message="Invalid phone number format")
     * @Assert\NotBlank
     */
    private $phone;

    /**
     * @ManyToMany(targetEntity="Skill", mappedBy="employees")
     */
    private $skills;

    /**
     * @OneToMany(targetEntity="Payment", mappedBy="employee", cascade={"persist", "merge", "detach", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $payments;

    /**
     * @OneToMany(targetEntity="Address", mappedBy="employee", cascade={"persist", "merge", "detach", "remove"}, orphanRemoval=true)
     * @Assert\Valid()
     */
    private $addresses;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    //region # Skills

    /**
     * @return Collection|Skill[]
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function setSkills(ArrayCollection $skills): void
    {
        $this->skills = $skills;
    }

    public function addSkill(Skill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addEmployee($this);
        }
    }

    public function removeSkill(Skill $skill): void
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
            $skill->removeEmployee($this);
        }
    }

    //endregion # Skills

    //region # Payments

    /**
     * @return Collection|Payment[]
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function setPayments(ArrayCollection $payments): void
    {
        $this->payments = $payments;
    }

    public function addPayment(Payment $payment): void
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setEmployee($this);
        }
    }

    public function removePayment(Payment $payment): void
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            $payment->setEmployee(null);
        }
    }

    //endregion # Payments

    //region # Addresses

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function setAddresses(ArrayCollection $addresses): void
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address $address): void
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setEmployee($this);
        }
    }

    public function removeAddress(Address $address): void
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            $address->setEmployee(null);
        }
    }

    //endregion # Addresses

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}

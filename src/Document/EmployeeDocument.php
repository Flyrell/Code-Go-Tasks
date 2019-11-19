<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Document;

use ArrayObject;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class EmployeeDocument extends AbstractDocument
{
    /**
     * @var string
     * @Assert\Length(min=1, max=50)
     * @Assert\NotBlank
     */
    private $firstName;

    /**
     * @var string
     * @Assert\Length(min=1, max=50)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @var DateTime
     */
    private $birthday;

    /**
     * @var string
     * @Assert\Email()
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @var string
     * @Assert\Regex("/^\(?\+?[0-9]{1,3}\)? ?-?[0-9]{1,3} ?-?[0-9]{3,5} ?-?[0-9]{3,4}( ?-?[0-9]{3})? ?(\w{1,10}\s?\d{1,6})?/", message="Invalid phone number format")
     * @Assert\NotBlank
     */
    private $phone;

    /**
     * @var iterable|ArrayObject|SkillDocument[]
     * @Assert\Valid
     */
    private $skills;

    /**
     * @var iterable|ArrayObject|PaymentDocument[]
     * @Assert\Valid
     */
    private $payments;

    /**
     * @var iterable|ArrayObject|AddressDocument[]
     * @Assert\Valid
     */
    private $addresses;

    public function __construct()
    {
        $this->skills = new ArrayObject();
        $this->payments = new ArrayObject();
        $this->addresses = new ArrayObject();
    }

    //region # Getters and setters

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return DateTimeInterface
     */
    public function getBirthday(): DateTimeInterface
    {
        return $this->birthday;
    }

    /**
     * @param DateTimeInterface $birthday
     */
    public function setBirthday(DateTimeInterface $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return SkillDocument[]|ArrayObject
     */
    public function getSkills(): ArrayObject
    {
        return $this->skills;
    }

    /**
     * @param SkillDocument[]|iterable $skills
     */
    public function setSkills(iterable $skills): void
    {
        $this->skills = new ArrayObject();

        foreach ($skills as $skill) {
            if (!$skill instanceof SkillDocument) {
                throw new InvalidArgumentException(sprintf('$skills must contain instances of type "%s"', SkillDocument::class));
            }

            $this->skills[] = $skill;
        }
    }

    /**
     * @Groups({"storage"})
     *
     * @return PaymentDocument[]|ArrayObject
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param PaymentDocument[]|iterable $payments
     */
    public function setPayments(iterable $payments): void
    {
        $this->payments = new ArrayObject();

        foreach ($payments as $payment) {
            if (!$payment instanceof PaymentDocument) {
                throw new InvalidArgumentException(sprintf('$payments must contain instances of type "%s"', PaymentDocument::class));
            }

            $this->payments[] = $payment;
        }
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return AddressDocument[]|ArrayObject
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AddressDocument[]|iterable $addresses
     */
    public function setAddresses(iterable $addresses): void
    {
        $this->addresses = new ArrayObject();

        foreach ($addresses as $address) {
            if (!$address instanceof AddressDocument) {
                throw new InvalidArgumentException(sprintf('$addresses must contain instances of type "%s"', AddressDocument::class));
            }

            $this->addresses[] = $address;
        }
    }

    //endregion # Getters and setters
}

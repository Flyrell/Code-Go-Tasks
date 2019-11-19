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

class AddressDocument extends AbstractDocument
{
    /**
     * @var string
     * @Assert\Length(min=2, max=100)
     * @Assert\NotBlank
     */
    private $city;

    /**
     * @var string
     * @Assert\Length(min=2, max=100)
     * @Assert\NotBlank
     */
    private $street;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $postalCode;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $country;

    //region # Getters and setters

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @Groups({"storage", "api"})
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    //endregion # Getters and setters
}

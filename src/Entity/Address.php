<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, max=100)
     * @Assert\NotBlank(message = "Please fill in name of the city.")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, max=100)
     * @Assert\NotBlank(message = "Please fill in name of the street.")
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\NotBlank(message = "Please fill in the postal code.")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\NotBlank(message = "Please fill in name of the country.")
     */
    private $country;

    /**
     * @ManyToOne(targetEntity="Employee", inversedBy="addresses")
     * @JoinColumn(name="employee_id", referencedColumnName="id")
     */
    private $employee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee = null): void
    {
        $this->employee = $employee;
    }
}

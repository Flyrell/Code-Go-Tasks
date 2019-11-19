<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Entity;

use App\Enum\SkillLevel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SkillRepository")
 * @ORM\Table(name="skill",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_level", columns={"name", "level"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Skill with this name already exists."
 * )
 */
class Skill
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\NotBlank(message = "Please fill in name of the skill.")
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1, max=5)
     */
    private $level = SkillLevel::LEVEL_BEGINNER;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $minSalary = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $maxSalary = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $avgSalary = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $medianSalary = 0;

    /**
     * @ManyToMany(targetEntity="Employee", inversedBy="skills")
     * @JoinTable(name="employees_skills")
     */
    private $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    public function getMinSalary(): float
    {
        return $this->minSalary;
    }

    public function setMinSalary(float $minSalary): void
    {
        $this->minSalary = $minSalary;
    }

    public function getMaxSalary(): float
    {
        return $this->maxSalary;
    }

    public function setMaxSalary(float $maxSalary): void
    {
        $this->maxSalary = $maxSalary;
    }

    public function getAvgSalary(): float
    {
        return $this->avgSalary;
    }

    public function setAvgSalary(float $avgSalary): void
    {
        $this->avgSalary = $avgSalary;
    }

    public function getMedianSalary(): float
    {
        return $this->medianSalary;
    }

    public function setMedianSalary(float $medianSalary): void
    {
        $this->medianSalary = $medianSalary;
    }

    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function setEmployees(ArrayCollection $employees): void
    {
        $this->employees = $employees;
    }

    public function addEmployee(Employee $employee): void
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->addSkill($this);
        }
    }

    public function removeEmployee(Employee $employee): void
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            $employee->removeSkill($this);
        }
    }
}

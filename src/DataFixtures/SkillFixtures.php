<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\DataFixtures;

use App\Entity\Skill;
use App\Enum\SkillLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SkillFixtures extends Fixture
{
    const SKILLS = [
        'SQL' => [
            'salary_min' => 35000,
            'salary_max' => 50000,
        ],
        'Java' => [
            'salary_min' => 70000,
            'salary_max' => 100000,
        ],
        'Javascript' => [
            'salary_min' => 40000,
            'salary_max' => 65000,
        ],
        'C#' => [
            'salary_min' => 40000,
            'salary_max' => 70000,
        ],
        'C++' => [
            'salary_min' => 60000,
            'salary_max' => 90000,
        ],
        'PHP' => [
            'salary_min' => 45000,
            'salary_max' => 70000,
        ],
        'Go' => [
            'salary_min' => 40000,
            'salary_max' => 75000,
        ],
        'Rust' => [
            'salary_min' => 50000,
            'salary_max' => 80000,
        ],
        'Project Management' => [
            'salary_min' => 40000,
            'salary_max' => 120000,
        ],
        'Node JS' => [
            'salary_min' => 40000,
            'salary_max' => 60000,
        ],
        'SEO' => [
            'salary_min' => 30000,
            'salary_max' => 60000,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SKILLS as $skillName => $skillConfig) {
            $salaryMin = $skillConfig['salary_min'];
            $salaryMax = $skillConfig['salary_max'];
            $salaryLevelReward = ($salaryMax - $salaryMin) / 5;

            foreach (SkillLevel::getAll() as $skillLevel) {
                $skill = new Skill();
                $skill->setName($skillName);
                $skill->setLevel($skillLevel);
                $skill->setMinSalary($this->roundToHundreds($salaryMin + ($salaryLevelReward * ($skill->getLevel() - 1))));
                $skill->setMaxSalary($this->roundToHundreds($salaryMin + ($salaryLevelReward * $skill->getLevel())));
                $skill->setAvgSalary(($skill->getMinSalary() + $skill->getMaxSalary()) / 2);
                $this->addSkillReference($skill);
                $manager->persist($skill);
            }
        }

        $manager->flush();
    }

    private function roundToHundreds(int $number): int
    {
        return round(($number / 100)) * 100;
    }

    /**
     * @param Skill $skill
     */
    private function addSkillReference(Skill $skill): void
    {
        $this->addReference(sprintf('skill:%s:%d', $skill->getName(), $skill->getLevel()), $skill);
    }
}

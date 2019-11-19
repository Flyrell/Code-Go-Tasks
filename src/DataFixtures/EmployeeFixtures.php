<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Employee;
use App\Entity\Payment;
use App\Entity\Skill;
use App\Service\EmployeeService;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use LogicException;

class EmployeeFixtures extends Fixture implements DependentFixtureInterface
{
    private const EMPLOYEES_TO_GENERATE = 1000;
    private const BATCH_SIZE = 50;
    private const LAST_PAYMENT_DATE = '01-12-2018';

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var EmployeeService
     */
    private $employeeService;

    /**
     * @var array
     */
    private $skillNames;

    /**
     * @param Generator       $faker
     * @param EmployeeService $employeeService
     */
    public function __construct(Generator $faker, EmployeeService $employeeService)
    {
        $this->faker = $faker;
        $this->employeeService = $employeeService;
        $this->skillNames = array_keys(SkillFixtures::SKILLS);
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::EMPLOYEES_TO_GENERATE; ++$i) {
            $employee = new Employee();
            $employee->setFirstName($this->faker->firstName);
            $employee->setLastName($this->faker->lastName);
            $employee->setEmail($this->faker->safeEmail);
            $employee->setPhone($this->faker->phoneNumber);
            $employee->setBirthday($this->faker->dateTimeBetween('-50 years', '-20 years'));

            $this->chooseSkills($employee);
            $this->generatePayments($employee);
            $this->generateAddresses($employee);

            $manager->persist($employee);

            if (0 === $i % self::BATCH_SIZE) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
    }

    /**
     * Choose skills and their levels for employee.
     *
     * @param Employee $employee
     */
    private function chooseSkills(Employee $employee): void
    {
        // How many skills will this employee have
        $randomSkillCount = mt_rand(1, 7);
        // Choose skills randomly from the array of possible skills
        $randomSkillKeys = array_rand($this->skillNames, $randomSkillCount);

        if (!is_array($randomSkillKeys)) {
            $randomSkillKeys = [$randomSkillKeys];
        }

        // Iterate randomly chosen skills, randomly select their level
        foreach ($randomSkillKeys as $randomSkillKey) {
            $skillName = $this->skillNames[$randomSkillKey];
            $randomSkillLevel = mt_rand(1, 5);
            $skill = $this->getSkillReference($skillName, $randomSkillLevel);
            $employee->addSkill($skill);
        }
    }

    /**
     * Generate history of payments for the employee.
     *
     * @param Employee $employee
     */
    private function generatePayments(Employee $employee): void
    {
        $howManyPaymentsToGenerate = mt_rand(12, 24);

        $topEmployeeSkills = $this->employeeService->getTopPaidSkills($employee, 3);
        $primarySkill = $topEmployeeSkills[0];

        // Calculate minimal salary and skill level reward by doing a weighted average of their top 3 skills
        $skillWeights = [0.85, 0.1, 0.05];

        $minSkillSalary = 0;
        $salaryLevelReward = 0;
        $i = 0;

        foreach ($topEmployeeSkills as $skill) {
            $skillWeight = $skillWeights[$i];
            $skillConfig = SkillFixtures::SKILLS[$skill->getName()];
            $salaryLevelReward += $skillWeight * (($skillConfig['salary_max'] - $skillConfig['salary_min'])) / 5 * $skill->getLevel();
            $minSkillSalary += $skillWeight * $skillConfig['salary_min'];
            ++$i;
        }

        $minSkillSalary = $this->roundToHundreds($minSkillSalary);

        // Now generate payments, with salary
        for ($i = $howManyPaymentsToGenerate - 1; 0 <= $i; --$i) {
            $salaryLevelRewardThisMonth = $this->roundToHundreds(mt_rand(0, $salaryLevelReward));
            $salary = $minSkillSalary + $salaryLevelRewardThisMonth;

            try {
                $month = new DateTime(self::LAST_PAYMENT_DATE);
                $month->sub(new DateInterval(sprintf('P%dM', $i)));
            } catch (\Exception $exception) {
                throw new LogicException('Failed to instantiate date of last payment.');
            }

            $payment = new Payment();
            $payment->setEmployee($employee);
            $payment->setPrimarySkill($primarySkill);
            $payment->setAmount($salary);
            $payment->setMonth($month);

            $employee->addPayment($payment);
        }
    }

    private function generateAddresses(Employee $employee)
    {
        $howManyAddressesToGenerate = mt_rand(1, 3);

        while ($howManyAddressesToGenerate--) {
            $payment = new Address();
            $payment->setEmployee($employee);
            $payment->setCountry('CZ');
            $payment->setCity($this->faker->city);
            $payment->setPostalCode($this->faker->postcode);
            $payment->setStreet($this->faker->streetAddress);

            $employee->addAddress($payment);
        }
    }

    private function getSkillReference(string $skillName, string $skillLevel): Skill
    {
        /** @var Skill $skill */
        $skill = $this->getReference(sprintf('skill:%s:%d', $skillName, $skillLevel));

        return $skill;
    }

    public function roundToHundreds(int $number): int
    {
        return round(($number / 100)) * 100;
    }

    public function getDependencies()
    {
        return [
            SkillFixtures::class,
        ];
    }
}

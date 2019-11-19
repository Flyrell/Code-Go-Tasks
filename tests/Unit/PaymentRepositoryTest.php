<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests\Unit;

use App\Entity\Employee;
use App\Entity\Payment;
use App\Entity\Skill;
use App\Repository\PaymentRepository;
use App\Repository\SkillRepository;
use App\Tests\AppTestCase;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\VarDumper\VarDumper;

class PaymentRepositoryTest extends AppTestCase
{
    /**
     * @throws Exception
     */
    public function testMedianService()
    {
        $now = new DateTime();
        $birthday = clone $now;
        $birthday->sub(new DateInterval('P20Y'));

        $skillName = 'Skill A';
        $skillA = new Skill();
        $skillA->setName($skillName);
        $skillA->setLevel(5);

        $paymentA = new Payment();
        $paymentA->setMonth($now);
        $paymentA->setPrimarySkill($skillA);
        $paymentA->setAmount(100000);

        $paymentB = new Payment();
        $paymentB->setMonth($now);
        $paymentB->setPrimarySkill($skillA);
        $paymentB->setAmount(50000);

        $employeeA = new Employee();
        $employeeA->setFirstName('TestA');
        $employeeA->setLastName('TestA');
        $employeeA->setEmail('test@a.cz');
        $employeeA->setPhone('111222333');
        $employeeA->setBirthday($birthday);
        $employeeA->addPayment($paymentA);

        $employeeB = new Employee();
        $employeeB->setFirstName('TestB');
        $employeeB->setLastName('TestB');
        $employeeB->setEmail('test@b.cz');
        $employeeB->setPhone('111222333');
        $employeeB->setBirthday($birthday);
        $employeeB->addPayment($paymentB);

        /** @var EntityManager $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $entityManager->persist($skillA);
        $entityManager->persist($employeeA);
        $entityManager->persist($employeeB);

        $entityManager->flush();

        /** @var SkillRepository $skillRepository */
        $skillRepository = self::$container->get(SkillRepository::class);

        $skill = $skillRepository->findOneBy(['name' => $skillName]);

        $this->assertNotNull($skill);

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = self::$container->get(PaymentRepository::class);

        $stats = $paymentRepository->getStatsForSkill($skill->getId());

        $this->assertEquals(100000, $stats['max'], 'Maximum salary for "Skill A" should be 100000');
        $this->assertEquals(75000, $stats['avg'], 'Average salary for "Skill A" should be 75000');
        $this->assertEquals(50000, $stats['min'], 'Minimum salary for "Skill A" should be 50000');
    }
}

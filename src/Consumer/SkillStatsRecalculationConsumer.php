<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Consumer;

use App\Consumer\Message\RecalculateSkillStatsMessage;
use App\Enum\SkillLevel;
use App\Repository\EmployeeRepository;
use App\Repository\PaymentRepository;
use App\Repository\SkillRepository;
use App\Service\EmployeeService;
use App\Service\MedianService;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SkillStatsRecalculationConsumer implements ConsumerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SerializerInterface
     */
    private $skillRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var EmployeeService
     */
    private $employeeService;

    /**
     * @var MedianService
     */
    private $medianService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        SkillRepository $skillRepository,
        EmployeeRepository $employeeRepository,
        PaymentRepository $paymentRepository,
        EmployeeService $employeeService,
        MedianService $medianService,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->skillRepository = $skillRepository;
        $this->employeeRepository = $employeeRepository;
        $this->paymentRepository = $paymentRepository;
        $this->employeeService = $employeeService;
        $this->medianService = $medianService;
        $this->logger = $logger;

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        /** @var RecalculateSkillStatsMessage $message */
        $message = $this->serializer->deserialize($msg->body, RecalculateSkillStatsMessage::class, 'json');

        // Prevent "MySQL server has gone away" exception
        $this->reconnectEntityManagerIfRequired();

        $skill = $this->skillRepository->find($message->getSkillId());

        if ($skill) {
            $skillName = SkillLevel::getLabel($skill->getLevel());
            $this->logger->debug(sprintf('Recalculating stats for skill [%s] at level [%s]', $skill->getName(), $skillName));
        } else {
            $this->logger->error(sprintf('Skill with id [%d] was not found in the database.', $skill->getName()));

            return true;
        }

        // Basic stats can be obtained directly from the database
        $updatedSkillStats = $this->paymentRepository->getStatsForSkill($skill->getId());

        $min = $updatedSkillStats['min'];
        $max = $updatedSkillStats['max'];
        $avg = $updatedSkillStats['avg'];

        // Others, like median, must be calculated manually
        $paymentAmounts = $this->paymentRepository->getPaymentAmountsForSkill($skill->getId());
        $median = $this->medianService->calculate($paymentAmounts);

        $this->logger->debug(sprintf('Recalculated skill [%s], new min=[%s], max=[%s], avg=[%s], median=[%s].', $skill->getName(), $min, $max, $avg, $median));

        $skill->setMinSalary($min);
        $skill->setMaxSalary($max);
        $skill->setAvgSalary($avg);
        $skill->setMedianSalary($median);

        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }

    /**
     * Prevents "MySQL server has gone away" exception.
     */
    private function reconnectEntityManagerIfRequired(): void
    {
        $connection = $this->entityManager->getConnection();
        if (false === $connection->ping()) {
            $connection->close();
            $connection->connect();
        }
    }
}

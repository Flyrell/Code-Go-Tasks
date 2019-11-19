<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Service;

use App\Consumer\Message\RecalculateSkillStatsMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProducerService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProducerInterface
     */
    private $skillStatsRecalculationProducer;

    public function __construct(
        SerializerInterface $serializer,
        ProducerInterface $skillStatsRecalculationProducer
    ) {
        $this->serializer = $serializer;
        $this->skillStatsRecalculationProducer = $skillStatsRecalculationProducer;
    }

    public function publishSkillStatsRecalculationMessage(int $skillId, string $sender = null)
    {
        $message = new RecalculateSkillStatsMessage($skillId, $sender);
        $msgBody = $this->serializer->serialize($message, 'json');

        $this->skillStatsRecalculationProducer->publish($msgBody);
    }
}

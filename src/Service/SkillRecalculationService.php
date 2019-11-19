<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Service;

use App\Entity\Employee;

class SkillRecalculationService
{
    /**
     * @var ProducerService
     */
    private $producerService;

    public function __construct(ProducerService $producerService)
    {
        $this->producerService = $producerService;
    }

    public function recalculateSkillsStatsOfEmployee(
        Employee $employee,
        string $sender = 'recalculate_skill_stats_of_employee'
    ) {
        foreach ($employee->getSkills() as $skill) {
            $this->producerService->publishSkillStatsRecalculationMessage($skill->getId(), $sender);
        }
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Service;

use App\Entity\Employee;
use App\Entity\Skill;

class EmployeeService
{
    /**
     * Gets up to N top paid skills of the employee.
     *
     * @param Employee $employee
     * @param int      $topPaidSkillCount
     *
     * @return Skill[]|array
     */
    public function getTopPaidSkills(Employee $employee, int $topPaidSkillCount = 3)
    {
        /** @var Skill[] $employeeSkills */
        $employeeSkills = $employee->getSkills()->toArray();

        // Sort employee skills by average of the skill salary
        uasort($employeeSkills, function (Skill $a, Skill $b) {
            return ($a->getAvgSalary() < $b->getAvgSalary()) ? 1 : -1;
        });

        // Pick up to 3 top paid skills
        return array_slice($employeeSkills, 0, $topPaidSkillCount);
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Saver;

use App\Entity\Employee;
use App\Repository\Elastic\EmployeeElasticRepository;
use App\Transformer\EmployeeDocumentTransformer;

class EmployeeSaver
{
    /**
     * @var EmployeeElasticRepository
     */
    private $employeeElasticRepository;

    /**
     * @var EmployeeDocumentTransformer
     */
    private $employeeDocumentTransformer;

    /**
     * @param EmployeeElasticRepository   $employeeElasticRepository
     * @param EmployeeDocumentTransformer $employeeDocumentTransformer
     */
    public function __construct(
        EmployeeElasticRepository $employeeElasticRepository,
        EmployeeDocumentTransformer $employeeDocumentTransformer
    ) {
        $this->employeeElasticRepository = $employeeElasticRepository;
        $this->employeeDocumentTransformer = $employeeDocumentTransformer;
    }

    public function save(Employee $employee): void
    {
        $employeeDocument = $this->employeeDocumentTransformer->transform($employee);
        $this->employeeElasticRepository->index($employeeDocument);
    }
}

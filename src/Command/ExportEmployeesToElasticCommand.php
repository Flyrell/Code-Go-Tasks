<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Command;

use App\Repository\Elastic\EmployeeElasticRepository;
use App\Repository\Elastic\Exception\BulkOperationException;
use App\Repository\EmployeeRepository;
use App\Transformer\EmployeeDocumentTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportEmployeesToElasticCommand extends Command
{
    protected static $defaultName = 'app:elastic:export-employees';

    const BATCH_SIZE = 100;

    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EmployeeRepository
     */
    private $employeeElasticRepository;

    /**
     * @var EmployeeDocumentTransformer
     */
    private $employeeDocumentTransformer;

    /**
     * @param EntityManagerInterface      $entityManager
     * @param EmployeeRepository          $employeeRepository
     * @param EmployeeElasticRepository   $employeeElasticRepository
     * @param EmployeeDocumentTransformer $employeeDocumentTransformer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EmployeeRepository $employeeRepository,
        EmployeeElasticRepository $employeeElasticRepository,
        EmployeeDocumentTransformer $employeeDocumentTransformer
    ) {
        $this->entityManager = $entityManager;
        $this->employeeRepository = $employeeRepository;
        $this->employeeElasticRepository = $employeeElasticRepository;
        $this->employeeDocumentTransformer = $employeeDocumentTransformer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Export employees from database to elastic')
            ->addArgument('id', InputArgument::OPTIONAL, 'Employee ID')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BulkOperationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $employeeId = $input->getArgument('id');

        if ($employeeId) {
            if ($employee = $this->employeeRepository->find($employeeId)) {
                $employeeDocument = $this->employeeDocumentTransformer->transform($employee);
                $this->employeeElasticRepository->index($employeeDocument);

                $io->success(sprintf('Employee #%s was exported to elastic', $employee->getId()));
            } else {
                $io->error(sprintf('Employee with id "%s" was not found in the database.', $employeeId));

                return 0;
            }
        } else {
            try {
                $maxEmployeeId = $this->employeeRepository->getMaxId();
            } catch (Exception $exception) {
                $output->writeln(sprintf('<comment>Failed to find maximum id in the Employee table, error was: <error>%s</error></comment>', $exception->getMessage()));

                return 1;
            }

            $output->writeln(sprintf("\n<comment>Max Employee id is <info>%d</info></comment>\n", $maxEmployeeId));

            $from = 0;
            $to = 0;
            while ($to <= $maxEmployeeId) {
                $to = $from + self::BATCH_SIZE;

                $iterableResult = $this->employeeRepository->findByIdRange($from, $to);

                $employeeDocuments = [];
                foreach ($iterableResult as $row) {
                    $employeeDocuments[] = $this->employeeDocumentTransformer->transform($row[0]);
                }

                $from += self::BATCH_SIZE;

                // If this id range was empty, no indexing is necessary
                if (empty($employeeDocuments)) {
                    continue;
                }

                $this->employeeElasticRepository->bulkIndex($employeeDocuments, false);
                $this->entityManager->clear();
            }

            $io->success('All Employees were exported to elastic');
        }

        return 0;
    }
}

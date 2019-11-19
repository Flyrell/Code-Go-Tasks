<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests;

use App\Repository\Elastic\EmployeeElasticRepository;
use App\Tests\Service\ExceptionFileLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AppTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var EmployeeElasticRepository */
    protected $employeeElasticRepository;

    /** @var Application */
    private $application;

    /** @var ExceptionFileLoggerService */
    private $exceptionFileLoggerService;

    /**
     * Set up empty in-memory sqlite database and separate elastic index for testing.
     */
    protected function setUp()
    {
        //echo "AppTestCase::setUp()" . PHP_EOL;
        $this->client = self::createClient()->getContainer();
        $kernel = self::$container->get('kernel');
        $this->exceptionFileLoggerService = self::$container->get('App\Tests\Service\ExceptionFileLoggerService');

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $em = self::$container->get(EntityManagerInterface::class);
        $this->executeCommand('doctrine:schema:drop --force');
        $em->getConnection()->exec('DROP TABLE IF EXISTS migration_versions');
        $this->executeCommand('doctrine:schema:create');

        $this->employeeElasticRepository = self::$container->get(EmployeeElasticRepository::class);

        if ($this->employeeElasticRepository->indexExists()) {
            $this->employeeElasticRepository->deleteIndex();
        } else {
            $this->employeeElasticRepository->createIndex();
        }

        $this->executeCommand('app:elastic:export-employees');
    }

    /**
     * @param string $command
     *
     * @return string
     */
    protected function executeCommand(string $command): string
    {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);

        try {
            $returnCode = $this->application->run($input, $output);
        } catch (Exception $exception) {
            echo $output->fetch();
            throw new RuntimeException(sprintf('Failed to execute command. Exception was: %s', $exception->getMessage()));
        }

        if (0 != $returnCode) {
            throw new RuntimeException('Failed to execute command. '.$output->fetch());
        }

        return $output->fetch();
    }

    /**
     * @param string $fileName
     * @param string $content
     */
    public function logExceptionToFile(string $fileName, string $content): void
    {
        $this->exceptionFileLoggerService->logExceptionToFile($this->getClassName().'/'.$fileName, $content);
    }

    private function getClassName(): string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ($this->employeeElasticRepository->indexExists()) {
            $this->employeeElasticRepository->deleteIndex();
        }
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Command;

use App\Repository\Elastic\Exception\IndexAlreadyExistsException;
use App\Repository\Elastic\Exception\IndexNotFoundException;
use App\Repository\Elastic\ElasticRepositoryInterface;
use App\DependencyInjection\ServiceLocator;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticIndexCommand extends Command
{
    protected static $defaultName = 'app:elastic:index';

    /**
     * @var ServiceLocator
     */
    private $repositories;

    /**
     * @param ServiceLocator $repositories
     */
    public function __construct(ServiceLocator $repositories)
    {
        $this->repositories = $repositories;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Creates an Elasticsearch index')
            ->addArgument('repository', InputArgument::OPTIONAL, 'Repository name')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force dropping an already existing index')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Reindex all repositories');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repositoryServiceName = $input->getArgument('repository');
        $force = $input->getOption('force');
        $all = $input->getOption('all');

        if (!$all && !$repositoryServiceName) {
            $this->printHelp($output);

            return;
        }

        if ($all) {
            foreach ($this->repositories->getServiceNames() as $repositoryServiceName) {
                $this->createIndex($repositoryServiceName, $force, $output);
            }
        } else {
            try {
                $this->createIndex($repositoryServiceName, $force, $output);
            } catch (InvalidArgumentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                $this->printHelp($output);
            }
        }
    }

    /**
     * Create index.
     *
     * @param string          $repositoryServiceName
     * @param bool            $force
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     */
    private function createIndex(string $repositoryServiceName, bool $force, OutputInterface $output)
    {
        if (!$this->repositories->has($repositoryServiceName)) {
            throw new InvalidArgumentException(sprintf('Invalid repository "%s"', $repositoryServiceName));
        }

        /** @var ElasticRepositoryInterface $repository */
        $repository = $this->repositories->get($repositoryServiceName);

        if ($force) {
            try {
                $repository->deleteIndex();
            } catch (IndexNotFoundException $e) {
            }
        }

        try {
            $repository->createIndex();
            $output->writeln(sprintf('<info>Index "%s" has been successfully created.</info>', $repositoryServiceName));
        } catch (IndexAlreadyExistsException $e) {
            $output->writeln(sprintf('<error>Index "%s" already exists. Use --force flag if you wish to re-create the index.</error>', $repositoryServiceName));
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function printHelp(OutputInterface $output): void
    {
        $output->writeln("\n<comment>Please provide a <info>valid repository</info> as first argument or use <info>--all</info> flag. Available repositories are:</comment>\n");
        foreach ($this->repositories->getServiceNames() as $repositoryServiceName) {
            $output->writeln(sprintf(' * <info>%s</info>', $repositoryServiceName));
        }
        $output->writeln('');
    }
}

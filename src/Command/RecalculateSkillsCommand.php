<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Command;

use App\Repository\SkillRepository;
use App\Service\ProducerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RecalculateSkillsCommand extends Command
{
    protected static $defaultName = 'app:skills:recalculate';

    /**
     * @var SkillRepository
     */
    private $skillRepository;

    /**
     * @var ProducerService
     */
    private $producerService;

    /**
     * @param SkillRepository $skillRepository
     * @param ProducerService $producerService
     */
    public function __construct(
        SkillRepository $skillRepository,
        ProducerService $producerService
    ) {
        $this->skillRepository = $skillRepository;
        $this->producerService = $producerService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Recalculate statistics for all skills and their skill levels');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $skillIds = $this->skillRepository->getAllIds();

        foreach ($skillIds as $skillId) {
            $this->producerService->publishSkillStatsRecalculationMessage($skillId, 'recalculate_skills_command');
        }

        $io->success(sprintf('All skills at all levels (a total of %d) were queued for recalculation', count($skillIds)));

        return 0;
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Transformer;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use App\Form\Model\SkillFormModel;
use Symfony\Component\Form\DataTransformerInterface;

class SkillSelectToSkillTransformer implements DataTransformerInterface
{
    /**
     * @var SkillRepository
     */
    private $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param Skill|null $value
     */
    public function transform($value)
    {
        $skillFormModel = new SkillFormModel();

        if ($value) {
            $skillFormModel->setName($value->getName());
            $skillFormModel->setLevel($value->getLevel());
        }

        return $skillFormModel;
    }

    /**
     * {@inheritdoc}
     *
     * @param SkillFormModel|null $value
     */
    public function reverseTransform($value)
    {
        return $this->skillRepository->findOneBy(['name' => $value->getName(), 'level' => $value->getLevel()]);
    }
}

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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Form\Model\SkillFormModel;
use Symfony\Component\Form\DataTransformerInterface;

class SkillSelectsToSkillsTransformer implements DataTransformerInterface
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
     * @param Collection|Skill[] $value
     */
    public function transform($value)
    {
        $skillFormModels = new ArrayCollection();

        foreach ($value as $item) {
            $skillFormModel = new SkillFormModel();
            $skillFormModel->setName($item->getName());
            $skillFormModel->setLevel($item->getLevel());
            $skillFormModels->add($skillFormModel);
        }

        return $skillFormModels;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $updatedSkills = new ArrayCollection();

        foreach ($value as $skillFromModel) {
            /* @var SkillFormModel $skillFromModel */
            /* @var Skill $skill */
            $skill = $this->skillRepository->findOneBy(['name' => $skillFromModel->getName(), 'level' => $skillFromModel->getLevel()]);

            if (!$skill) {
                continue;
            }

            // Remove duplicate skills
            if (!$updatedSkills->contains($skill)) {
                $updatedSkills->add($skill);
            }
        }

        return $updatedSkills;
    }
}

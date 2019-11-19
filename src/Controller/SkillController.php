<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Controller;

use App\Entity\Skill;
use App\Enum\SkillLevel;
use App\Form\NewSkillType;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/skill")
 */
class SkillController extends AbstractController
{
    /**
     * @Route("/", name="skill_index", methods={"GET"})
     *
     * @param SkillRepository $skillRepository
     *
     * @return Response
     */
    public function index(SkillRepository $skillRepository): Response
    {
        return $this->render('skill/index.html.twig', ['skills' => $skillRepository->findAllOrdered()]);
    }

    /**
     * @Route("/new", name="skill_new", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $skillBeginner = new Skill();
        $skillBeginner->setLevel(SkillLevel::LEVEL_BEGINNER);

        $form = $this->createForm(NewSkillType::class, $skillBeginner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $skillIntermediate = new Skill();
            $skillAdvanced = new Skill();
            $skillExpert = new Skill();
            $skillMaster = new Skill();

            $skillIntermediate->setLevel(SkillLevel::LEVEL_INTERMEDIATE);
            $skillAdvanced->setLevel(SkillLevel::LEVEL_ADVANCED);
            $skillExpert->setLevel(SkillLevel::LEVEL_EXPERT);
            $skillMaster->setLevel(SkillLevel::LEVEL_MASTER);

            $skillIntermediate->setName($skillBeginner->getName());
            $skillAdvanced->setName($skillBeginner->getName());
            $skillExpert->setName($skillBeginner->getName());
            $skillMaster->setName($skillBeginner->getName());

            $entityManager->persist($skillBeginner);
            $entityManager->persist($skillIntermediate);
            $entityManager->persist($skillAdvanced);
            $entityManager->persist($skillExpert);
            $entityManager->persist($skillMaster);

            $entityManager->flush();

            return $this->redirectToRoute('skill_index');
        }

        return $this->render('skill/new.html.twig', [
            'skill' => $skillBeginner,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="skill_view", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @param Skill $skill
     *
     * @return Response
     */
    public function view(Skill $skill): Response
    {
        return $this->render('skill/view.html.twig', ['skill' => $skill]);
    }

    /**
     * @Route("/{id}", name="skill_delete", methods={"DELETE"})
     *
     * @param Request         $request
     * @param Skill           $skill
     * @param SkillRepository $skillRepository
     *
     * @return Response
     */
    public function delete(Request $request, Skill $skill, SkillRepository $skillRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$skill->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $skillsToRemove = $skillRepository->findBy(['name' => $skill->getName()]);
            foreach ($skillsToRemove as $skillToRemove) {
                $entityManager->remove($skillToRemove);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('skill_index');
    }

    /**
     * @Route("/autocomplete", name="skill_autocomplete", methods={"GET"})
     *
     * @param Request         $request
     * @param SkillRepository $skillRepository
     *
     * @return JsonResponse
     */
    public function autocomplete(Request $request, SkillRepository $skillRepository): Response
    {
        $search = $request->get('search', '');

        $skills = $skillRepository->autocomplete($search);

        return $this->json($skills, 200, [], ['options' => true]);
    }

    /**
     * @Route("/find-by-id", name="skill_find_by_id", methods={"GET"})
     *
     * @param Request         $request
     * @param SkillRepository $skillRepository
     *
     * @return JsonResponse
     */
    public function findById(Request $request, SkillRepository $skillRepository): Response
    {
        $ids = $request->get('ids', '');

        $skills = $skillRepository->findBy(['id' => $ids]);

        return $this->json($skills, 200, [], ['options' => true]);
    }
}

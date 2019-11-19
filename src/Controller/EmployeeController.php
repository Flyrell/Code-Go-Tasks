<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Helper\RequestHelper;
use App\Repository\Elastic\EmployeeElasticRepository;
use App\Saver\EmployeeSaver;
use App\Service\SkillRecalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/employee")
 */
class EmployeeController extends AbstractController
{
    /**
     * @Route("/", name="employee_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('employee/search.html.twig', [
            'employeeTableConfig' => [
                'endpoints' => [
                    'employee' => [
                        'search' => $this->generateUrl('employee_search'),
                        'edit' => str_replace('99999', '{id}', $this->generateUrl('employee_edit', ['id' => 99999])),
                        'new' => $this->generateUrl('employee_new'),
                    ],
                    'skill' => [
                        'autocomplete' => $this->generateUrl('skill_autocomplete'),
                        'findByIds' => $this->generateUrl('skill_find_by_id'),
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Route("/list", name="employee_search", methods={"GET"})
     *
     * @param Request                   $request
     * @param RequestHelper             $requestHelper
     * @param EmployeeElasticRepository $employeeElasticRepository
     *
     * @return Response
     */
    public function listAction(
        Request $request,
        RequestHelper $requestHelper,
        EmployeeElasticRepository $employeeElasticRepository
    ): Response {
        $query = $request->query;
        $criteria = [
            'search' => $query->filter('search'),
            'skills' => array_map('intval', $query->filter('skills', [])),
        ];
        list($offset, $limit) = $requestHelper->getOffsetLimit($request);
        $sort = $query->filter('sort');
        $employees = $employeeElasticRepository->search($criteria, $offset, $limit, $sort);

        return $this->json($employees, 200, [], ['groups' => ['api']]);
    }

    /**
     * @Route("/new", name="employee_new", methods={"GET","POST"})
     *
     * @param Request                   $request
     * @param SkillRecalculationService $skillRecalculationService
     * @param EmployeeSaver             $employeeSaver
     *
     * @return Response
     */
    public function new(
        Request $request,
        SkillRecalculationService $skillRecalculationService,
        EmployeeSaver $employeeSaver
    ): Response {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($employee);
            $entityManager->flush();
            $employeeSaver->save($employee);

            $skillRecalculationService->recalculateSkillsStatsOfEmployee($employee, 'employee_new');

            return $this->redirectToRoute('employee_index');
        }

        return $this->render('employee/new.html.twig', [
            'employee' => $employee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="employee_edit", methods={"GET","POST"})
     *
     * @param Request                   $request
     * @param Employee                  $employee
     * @param SkillRecalculationService $skillRecalculationService
     * @param EmployeeSaver             $employeeSaver
     *
     * @return Response
     */
    public function edit(
        Request $request,
        Employee $employee,
        SkillRecalculationService $skillRecalculationService,
        EmployeeSaver $employeeSaver
    ): Response {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $employeeSaver->save($employee);

            $skillRecalculationService->recalculateSkillsStatsOfEmployee($employee, 'employee_edit');

            return $this->redirectToRoute('employee_index', ['id' => $employee->getId()]);
        }

        return $this->render('employee/edit.html.twig', [
            'employee' => $employee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="employee_delete", methods={"DELETE"})
     *
     * @param Request                   $request
     * @param Employee                  $employee
     * @param EmployeeElasticRepository $employeeElasticRepository
     * @param SkillRecalculationService $skillRecalculationService
     *
     * @return Response
     */
    public function delete(
        Request $request,
        Employee $employee,
        EmployeeElasticRepository $employeeElasticRepository,
        SkillRecalculationService $skillRecalculationService
    ): Response {
        $employeeId = $employee->getId();
        if ($this->isCsrfTokenValid('delete'.$employeeId, $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($employee);
            $entityManager->flush();
            $employeeElasticRepository->deleteById($employeeId);
        }

        return $this->redirectToRoute('employee_index');
    }
}

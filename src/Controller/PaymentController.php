<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Controller;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/", name="payment_index", methods={"GET"})
     *
     * @param PaymentRepository $paymentRepository
     *
     * @return Response
     */
    public function index(PaymentRepository $paymentRepository): Response
    {
        // TODO: Possible task - add pagination

        return $this->render('payment/index.html.twig', ['results' => $paymentRepository->findAllOrdered()]);
    }

    /**
     * @Route("/{id}", name="payment_view", methods={"GET"})
     *
     * @param Payment $payment
     *
     * @return Response
     */
    public function view(Payment $payment): Response
    {
        return $this->render('payment/view.html.twig', ['payment' => $payment]);
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Transformer;

use App\Document\AddressDocument;
use App\Document\EmployeeDocument;
use App\Document\PaymentDocument;
use App\Document\SkillDocument;
use App\Entity\Employee;
use App\Entity\Skill;

class EmployeeDocumentTransformer
{
    public function transform(Employee $employee): EmployeeDocument
    {
        $employeeDocument = new EmployeeDocument();

        $employeeDocument->setId($employee->getId());
        $employeeDocument->setFirstName($employee->getFirstName());
        $employeeDocument->setLastName($employee->getLastName());
        $birthday = $employee->getBirthday();
        $employeeDocument->setBirthday($birthday ? clone $birthday : null);
        $employeeDocument->setEmail($employee->getEmail());
        $employeeDocument->setPhone($employee->getPhone());

        $skillDocuments = $this->transformSkills($employee->getSkills());
        $addressDocuments = $this->transformAddresses($employee->getAddresses());
        $paymentDocuments = $this->transformPayments($employee->getPayments());

        $employeeDocument->setSkills($skillDocuments);
        $employeeDocument->setAddresses($addressDocuments);
        $employeeDocument->setPayments($paymentDocuments);

        return $employeeDocument;
    }

    /**
     * @param Skill[]|iterable $skills
     *
     * @return array
     */
    private function transformSkills(iterable $skills): array
    {
        $skillDocuments = [];
        foreach ($skills as $skill) {
            $skillDocument = new SkillDocument();
            $skillDocument->setId($skill->getId());
            $skillDocument->setName($skill->getName());
            $skillDocument->setLevel($skill->getLevel());

            $skillDocuments[] = $skillDocument;
        }

        return $skillDocuments;
    }

    /**
     * @param AddressDocument[]|iterable $addresses
     *
     * @return array
     */
    private function transformAddresses(iterable $addresses): array
    {
        $addressDocuments = [];
        foreach ($addresses as $address) {
            $addressDocument = new AddressDocument();
            $addressDocument->setId($address->getId());
            $addressDocument->setCountry($address->getCountry());
            $addressDocument->setCity($address->getCity());
            $addressDocument->setStreet($address->getStreet());
            $addressDocument->setPostalCode($address->getPostalCode());

            $addressDocuments[] = $addressDocument;
        }

        return $addressDocuments;
    }

    /**
     * @param PaymentDocument[]|iterable $skills
     *
     * @return array
     */
    private function transformPayments(iterable $skills): array
    {
        $paymentDocuments = [];
        foreach ($skills as $skill) {
            $paymentDocument = new PaymentDocument();
            $paymentDocument->setId($skill->getId());
            $paymentDocument->setMonth($skill->getMonth());
            $paymentDocument->setAmount($skill->getAmount());

            $paymentDocuments[] = $paymentDocument;
        }

        return $paymentDocuments;
    }
}

<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests\Smoke;

use App\Tests\AppTestCase;

class ApplicationAvailabilityFunctionalTest extends AppTestCase
{
    /**
     * @dataProvider urlProvider
     *
     * @param string $url
     * @param string $alias
     */
    public function testPageIsSuccessful(string $url, string $alias)
    {
        $client = self::createClient();
        $client->request('GET', $url);
        $response = $client->getResponse();
        $responseCode = $response->getStatusCode();

        if (500 === $responseCode) {
            $this->logExceptionToFile(sprintf('%s.exception.html', $alias), $response->getContent());
        }

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        yield ['/', 'home'];
        yield ['/employee/', 'employee_index'];
        yield ['/employee/list', 'employee_list'];
        yield ['/employee/new', 'employee_new'];
        yield ['/skill/', 'skill_index'];
        yield ['/skill/new', 'skill_new'];
        yield ['/skill/autocomplete', 'skill_autocomplete'];
    }
}

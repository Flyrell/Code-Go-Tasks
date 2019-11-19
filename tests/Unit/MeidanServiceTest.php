<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests\Unit;

use App\Service\MedianService;
use PHPUnit\Framework\TestCase;

class MedianServiceTest extends TestCase
{
    public function testMedianService()
    {
        $valuesAndExpectedResults = [
            [[1, 2, 2, 2, 3, 9], 2],
            [[6, 7, 1, 2, 3, 4, 5], 4],
            [[20, 30, 45, 60, 76, 90, 106], 60],
            [[90, 106, 156, 180, 20, 30, 34, 34, 76], 76],
            [[2, 3], 2.5],
            [[2, 2, 3, 3], 2.5],
            [[1, 2, 2, 3, 3, 9], 2.5],
            [[1, 2, 2, 2, 3, 9, 10, 100], 2.5],
        ];

        $medianService = new MedianService();

        foreach ($valuesAndExpectedResults as $valuesAndExpectedResult) {
            $this->assertEquals(
                $valuesAndExpectedResult[1],
                $medianService->calculate($valuesAndExpectedResult[0]),
                sprintf(
                    'Expected median of [%s] to be [%s]',
                    implode($valuesAndExpectedResult[0], ', '),
                    $valuesAndExpectedResult[1]
                )
            );
        }
    }
}

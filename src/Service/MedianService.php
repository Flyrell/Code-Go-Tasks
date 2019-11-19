<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Service;

class MedianService
{
    public function calculate(array $values): float
    {
        sort($values);
        $count = count($values);

        if (0 === $count) {
            return 0;
        } elseif (0 === $count % 2) {
            $middle = (int) floor($count / 2);

            // If there are two numbers in the middle, use their arithmetic average
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            $middle = (int) floor($count / 2);

            return $values[$middle];
        }
    }
}

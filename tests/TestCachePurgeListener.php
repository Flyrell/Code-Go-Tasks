<?php

/*
* Copyright (C) 2018 Techpike s.r.o.
* All Rights Reserved.
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of this source code package.
*/

namespace App\Tests;

use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;

class TestCachePurgeListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @var bool
     */
    private $cacheCleared = false;

    public function startTestSuite(TestSuite $suite)
    {
        if (false === $this->cacheCleared) {
            // Clearing cache because of ´Fatal error: Allowed memory size exhausted´ in GlobResource.php on line 104
            printf('Clearing test cache'.PHP_EOL);
            exec('php bin/console cache:clear --env=test');
            $this->cacheCleared = true;
        }
    }
}

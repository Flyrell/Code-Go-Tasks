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
use Symfony\Component\Filesystem\Filesystem;

class TestExceptionLogPurgeListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @var bool
     */
    private $exceptionLogsCleared = false;

    public function startTestSuite(TestSuite $suite)
    {
        if (false === $this->exceptionLogsCleared) {
            printf('Clearing test exception logs'.PHP_EOL);

            $fileSystem = new Filesystem();
            $fileSystem->remove(__DIR__.'/../var/log/test/');

            $this->exceptionLogsCleared = true;
        }
    }
}

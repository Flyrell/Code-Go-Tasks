<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Tests\Service;

use Symfony\Component\Filesystem\Filesystem;

class ExceptionFileLoggerService
{
    /** @var Filesystem */
    private $fileSystem;

    /** @var string */
    private $exceptionDir;

    public function __construct(string $projectDir)
    {
        $this->exceptionDir = $projectDir.'/var/log/test/';
        $this->fileSystem = new Filesystem();
    }

    /**
     * @param string $fileName
     * @param string $content
     */
    public function logExceptionToFile(string $fileName, string $content): void
    {
        $this->fileSystem->mkdir(dirname($fileName));
        $this->fileSystem->dumpFile($this->exceptionDir.$fileName, $content);
    }

}

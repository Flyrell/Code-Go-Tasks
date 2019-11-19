<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Repository\Elastic\Exception;

use Exception;
use Throwable;

class BulkOperationException extends Exception
{
    /**
     * @var array
     */
    private $response;

    /**
     * BulkOperationException constructor.
     *
     * @param array          $response
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(array $response, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}

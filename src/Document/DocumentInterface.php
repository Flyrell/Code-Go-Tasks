<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Document;

interface DocumentInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     *
     * @return DocumentInterface
     */
    public function setId(int $id): void;
}

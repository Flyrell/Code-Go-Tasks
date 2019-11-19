<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\DoctrineExtensions\Hydrators\Mysql;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class ColumnHydratorInt extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        return array_map('intval', $this->_stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}

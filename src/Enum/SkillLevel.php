<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Enum;

class SkillLevel
{
    const LEVEL_BEGINNER = 1;
    const LEVEL_INTERMEDIATE = 2;
    const LEVEL_ADVANCED = 3;
    const LEVEL_EXPERT = 4;
    const LEVEL_MASTER = 5;

    const LABEL_LEVEL_BEGINNER = 'Beginner';
    const LABEL_LEVEL_INTERMEDIATE = 'Intermediate';
    const LABEL_LEVEL_ADVANCED = 'Advanced';
    const LABEL_LEVEL_EXPERT = 'Expert';
    const LABEL_LEVEL_MASTER = 'Master';

    private static $labels = [
        self::LEVEL_BEGINNER => self::LABEL_LEVEL_BEGINNER,
        self::LEVEL_INTERMEDIATE => self::LABEL_LEVEL_INTERMEDIATE,
        self::LEVEL_ADVANCED => self::LABEL_LEVEL_ADVANCED,
        self::LEVEL_EXPERT => self::LABEL_LEVEL_EXPERT,
        self::LEVEL_MASTER => self::LABEL_LEVEL_MASTER,
    ];

    public static function getLabel(int $value): string
    {
        return self::$labels[$value];
    }

    public static function getChoices()
    {
        return [
            self::LABEL_LEVEL_BEGINNER => self::LEVEL_BEGINNER,
            self::LABEL_LEVEL_INTERMEDIATE => self::LEVEL_INTERMEDIATE,
            self::LABEL_LEVEL_ADVANCED => self::LEVEL_ADVANCED,
            self::LABEL_LEVEL_EXPERT => self::LEVEL_EXPERT,
            self::LABEL_LEVEL_MASTER => self::LEVEL_MASTER,
        ];
    }

    public static function getAll()
    {
        return [
            self::LEVEL_BEGINNER,
            self::LEVEL_INTERMEDIATE,
            self::LEVEL_ADVANCED,
            self::LEVEL_EXPERT,
            self::LEVEL_MASTER,
        ];
    }
}

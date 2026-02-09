<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * A utility class with static methods.
 * Used as a target for forbidden class-level static calls.
 */
class StaticHelper
{
    public static function calculate(): int
    {
        return 42;
    }

    public static function format(): string
    {
        return 'formatted';
    }
}

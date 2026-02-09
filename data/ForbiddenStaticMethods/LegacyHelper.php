<?php

declare(strict_types=1);

namespace App\Legacy;

/**
 * A legacy helper class with static methods.
 * Used as a target for forbidden namespace-level static calls.
 */
class LegacyHelper
{
    public static function doSomething(): string
    {
        return 'legacy';
    }
}

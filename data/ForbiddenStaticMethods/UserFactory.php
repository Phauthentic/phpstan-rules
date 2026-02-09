<?php

declare(strict_types=1);

namespace App\Factory;

/**
 * A factory class with static methods.
 * Used as an allowed static call target.
 */
class UserFactory
{
    public static function create(): object
    {
        return new \stdClass();
    }
}

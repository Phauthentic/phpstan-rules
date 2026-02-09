<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\StaticHelper;

/**
 * Service class that makes a forbidden static call to a forbidden class.
 */
class ForbiddenClassStaticCall
{
    public function execute(): int
    {
        // This static call should be forbidden (class-level pattern)
        return StaticHelper::calculate();
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;

/**
 * Service class that makes a forbidden static call to a specific method.
 */
class ForbiddenMethodStaticCall
{
    public function execute(): DateTime
    {
        // This static call should be forbidden (method-level pattern)
        return DateTime::createFromFormat('Y-m-d', '2024-01-01');
    }
}

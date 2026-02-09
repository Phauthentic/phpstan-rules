<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;

/**
 * Service class that makes an allowed static call to a method on DateTime.
 * Only DateTime::createFromFormat is forbidden, not other methods.
 */
class AllowedMethodOnForbiddenClass
{
    public function execute(): string
    {
        // This static call should be allowed (only createFromFormat is forbidden)
        return DateTime::getLastErrors() !== false ? 'errors' : 'no errors';
    }
}

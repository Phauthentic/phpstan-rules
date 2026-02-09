<?php

declare(strict_types=1);

namespace App\Service;

use App\Legacy\LegacyHelper;

/**
 * Service class that makes a forbidden static call to a class in the Legacy namespace.
 */
class ForbiddenNamespaceStaticCall
{
    public function execute(): string
    {
        // This static call should be forbidden (namespace-level pattern)
        return LegacyHelper::doSomething();
    }
}

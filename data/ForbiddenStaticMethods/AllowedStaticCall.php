<?php

declare(strict_types=1);

namespace App\Service;

use App\Factory\UserFactory;

/**
 * Service class that makes an allowed static call.
 */
class AllowedStaticCall
{
    public function execute(): object
    {
        // This static call should be allowed (not matching any forbidden pattern)
        return UserFactory::create();
    }
}

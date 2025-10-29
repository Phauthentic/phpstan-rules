<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application;

use App\Capability\UserManagement\Infrastructure\InvalidInfra;

/**
 * This would normally be invalid (Application importing Infrastructure)
 * But with custom configuration allowing Application → Infrastructure, it becomes valid
 */
class ValidWithCustomConfig
{
    public function __construct(
        private InvalidInfra $infra
    ) {
    }
}


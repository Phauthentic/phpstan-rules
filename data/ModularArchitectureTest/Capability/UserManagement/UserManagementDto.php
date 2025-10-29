<?php

declare(strict_types=1);

namespace App\Capability\UserManagement;

/**
 * A DTO class that would normally not be allowed for cross-module import
 * (with default config), but can be allowed with custom patterns
 */
class UserManagementDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {
    }
}


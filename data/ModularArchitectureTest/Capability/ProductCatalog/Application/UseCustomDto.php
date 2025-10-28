<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Application;

use App\Capability\UserManagement\UserManagementDto;

/**
 * This imports a DTO class from another module.
 * - With default config: This is INVALID (only Facade, FacadeInterface, Input, Result allowed)
 * - With custom pattern '/Dto$/': This is VALID
 */
class UseCustomDto
{
    public function __construct(
        private UserManagementDto $dto
    ) {
    }
}


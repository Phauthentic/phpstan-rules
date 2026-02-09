<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application;

use App\Capability\UserManagement\UserManagementFacade;

class SameModuleImport
{
    public function __construct(
        private UserManagementFacade $facade
    ) {
    }
}

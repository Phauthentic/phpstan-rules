<?php

declare(strict_types=1);

namespace App\NonModular;

use App\Capability\UserManagement\UserManagementFacade;

class OutsideClass
{
    public function __construct(
        private UserManagementFacade $facade
    ) {
    }
}

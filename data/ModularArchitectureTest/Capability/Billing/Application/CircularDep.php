<?php

declare(strict_types=1);

namespace App\Capability\Billing\Application;

use App\Capability\UserManagement\UserManagementFacade;

/**
 * Creates circular dependency:
 * Billing → UserManagement
 * UserManagement would depend on ProductCatalog (in ValidCrossModule)
 * If we make ProductCatalog depend on Billing, we get a cycle
 */
class CircularDep
{
    public function __construct(
        private UserManagementFacade $userManagement
    ) {
    }
}


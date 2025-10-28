<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application;

use App\Capability\ProductCatalog\ProductCatalogFacade;

/**
 * This completes the circular dependency:
 * - ProductCatalog → Billing (CreateCircular.php in ProductCatalog, analyzed first)
 * - Billing → UserManagement (CircularDep.php in Billing, analyzed second)
 * - UserManagement → ProductCatalog (this file, analyzed third, creates cycle)
 *
 * Expected cycle: ProductCatalog → Billing → UserManagement → ProductCatalog
 */
class CreateCircularToUserManagement
{
    public function __construct(
        private ProductCatalogFacade $productCatalog
    ) {
    }
}


<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Application;

use App\Capability\Billing\BillingFacade;

/**
 * This creates a circular dependency when combined with Billing/Application/CircularDep.php:
 * ProductCatalog → Billing → UserManagement → ProductCatalog (via ValidCrossModule)
 * 
 * Wait, let me reconsider. ValidCrossModule has:
 * ProductCatalog → UserManagement
 * 
 * CircularDep has:
 * Billing → UserManagement
 * 
 * This file has:
 * ProductCatalog → Billing
 * 
 * So we don't quite have a cycle yet. Let me think...
 * We need: A → B → C → A
 * 
 * Let's make it simpler:
 * - ProductCatalog → UserManagement (ValidCrossModule)
 * - UserManagement → Billing (need to create this)
 * - Billing → ProductCatalog (this file creates it)
 */
class CreateCircular
{
    public function __construct(
        private BillingFacade $billing
    ) {
    }
}


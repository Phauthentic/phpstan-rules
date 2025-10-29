<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Application;

use App\Capability\UserManagement\UserManagementException;

/**
 * Invalid: Importing exception from another module
 */
class InvalidCrossModule
{
    public function __construct()
    {
    }
}


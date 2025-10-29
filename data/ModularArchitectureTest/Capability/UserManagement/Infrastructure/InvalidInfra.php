<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Infrastructure;

use App\Capability\UserManagement\Presentation\AdminAPI\Controller\UserController;

/**
 * Invalid: Infrastructure layer importing from Presentation layer
 */
class InvalidInfra
{
    public function __construct()
    {
    }
}


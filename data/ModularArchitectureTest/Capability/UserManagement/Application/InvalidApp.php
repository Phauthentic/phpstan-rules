<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application;

use App\Capability\UserManagement\Presentation\AdminAPI\Controller\UserController;

/**
 * Invalid: Application layer importing from Presentation layer
 */
class InvalidApp
{
    public function __construct()
    {
    }
}


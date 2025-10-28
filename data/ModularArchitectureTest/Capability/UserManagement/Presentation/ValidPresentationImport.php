<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Presentation;

use App\Capability\UserManagement\Presentation\AdminAPI\Controller\UserController;

/**
 * Valid: Presentation layer importing from Presentation layer (same layer)
 */
class ValidPresentationImport
{
    public function __construct(
        private UserController $controller
    ) {
    }
}


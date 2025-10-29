<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Presentation\AdminAPI\Controller;

use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUser;

/**
 * Valid controller - Presentation can import from Application
 */
class UserController
{
    public function __construct(
        private CreateUser $createUser
    ) {
    }

    public function create(): void
    {
        // Controller logic
    }
}


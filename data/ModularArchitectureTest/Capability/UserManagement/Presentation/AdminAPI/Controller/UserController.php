<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Presentation\AdminAPI\Controller;

use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUser;
use App\Capability\UserManagement\Domain\Model\User;

/**
 * Valid controller - Presentation can import from Application and Domain
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


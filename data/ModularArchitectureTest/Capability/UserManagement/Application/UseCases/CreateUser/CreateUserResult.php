<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application\UseCases\CreateUser;

use App\Capability\UserManagement\Domain\Model\User;

/**
 * Valid Result DTO - can be imported cross-module
 */
class CreateUserResult
{
    public function __construct(
        public readonly User $user
    ) {
    }
}


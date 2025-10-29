<?php

declare(strict_types=1);

namespace App\Capability\UserManagement;

use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUser;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserInput;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserResult;

/**
 * Valid Facade - can be imported cross-module
 */
class UserManagementFacade implements UserManagementFacadeInterface
{
    public function __construct(
        private CreateUser $createUser
    ) {
    }

    public function createUser(CreateUserInput $input): CreateUserResult
    {
        return $this->createUser->execute($input);
    }
}


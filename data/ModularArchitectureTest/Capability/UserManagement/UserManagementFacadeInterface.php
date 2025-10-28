<?php

declare(strict_types=1);

namespace App\Capability\UserManagement;

use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserInput;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserResult;

/**
 * Valid Facade Interface - can be imported cross-module
 */
interface UserManagementFacadeInterface
{
    public function createUser(CreateUserInput $input): CreateUserResult;
}


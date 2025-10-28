<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Application;

use App\Capability\UserManagement\UserManagementFacade;
use App\Capability\UserManagement\UserManagementFacadeInterface;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserInput;
use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUserResult;

/**
 * Valid cross-module imports: Facade, FacadeInterface, Input, Result
 */
class ValidCrossModule
{
    public function __construct(
        private UserManagementFacadeInterface $userManagement
    ) {
    }

    public function doSomething(): void
    {
        $input = new CreateUserInput('123', 'John Doe');
        $result = $this->userManagement->createUser($input);
    }
}


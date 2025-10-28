<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application\UseCases\CreateUser;

use App\Capability\UserManagement\Domain\Model\User;

/**
 * Valid use case - Application can import from Domain
 */
class CreateUser
{
    public function execute(CreateUserInput $input): CreateUserResult
    {
        $user = new User($input->id, $input->name);
        
        return new CreateUserResult($user);
    }
}


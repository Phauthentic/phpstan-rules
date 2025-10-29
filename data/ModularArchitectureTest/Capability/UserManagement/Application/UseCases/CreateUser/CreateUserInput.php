<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Application\UseCases\CreateUser;

/**
 * Valid Input DTO - can be imported cross-module
 */
class CreateUserInput
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {
    }
}


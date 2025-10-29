<?php

declare(strict_types=1);

namespace App\Capability\UserManagement\Domain;

use App\Capability\UserManagement\Application\UseCases\CreateUser\CreateUser;

/**
 * Invalid: Domain layer importing from Application layer
 */
class InvalidDomain
{
    public function __construct()
    {
    }
}


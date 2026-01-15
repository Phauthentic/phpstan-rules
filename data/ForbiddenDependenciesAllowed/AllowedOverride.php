<?php

declare(strict_types=1);

namespace App\Capability\Billing\Domain;

// These are forbidden by the "forbid everything" pattern but allowed by the whitelist
use App\Shared\ValueObject\Money;
use App\Capability\UserManagement\UserManagementFacade;
use Psr\Log\LoggerInterface;

/**
 * Test class that uses dependencies which are forbidden but overridden by allowedDependencies.
 * None of these should trigger errors because they match the allowed patterns.
 */
class AllowedOverride
{
    private Money $amount;

    private LoggerInterface $logger;

    public function __construct(Money $amount, LoggerInterface $logger)
    {
        $this->amount = $amount;
        $this->logger = $logger;
    }

    public function getFacade(): UserManagementFacade
    {
        return new \App\Capability\UserManagement\UserManagementFacade();
    }

    public function logSomething(): void
    {
        $this->logger->info('test');
    }
}

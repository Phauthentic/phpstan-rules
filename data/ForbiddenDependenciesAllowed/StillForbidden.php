<?php

declare(strict_types=1);

namespace App\Capability\Billing\Domain;

// These are forbidden and NOT in the allowed list - should trigger errors
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test class that uses dependencies which are forbidden and NOT overridden.
 * These should trigger errors.
 */
class StillForbidden
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handleRequest(Request $request): void
    {
        // Using forbidden dependency
    }

    public function createEntityManager(): EntityManager
    {
        return new \Doctrine\ORM\EntityManager();
    }
}

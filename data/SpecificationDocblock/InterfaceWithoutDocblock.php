<?php

declare(strict_types=1);

namespace App\Service;

// Interface without docblock - should trigger error when matching classPatterns
interface ServiceInterface
{
    public function execute(): void;
}

// Interface with proper docblock - should NOT trigger error
/**
 * Specification:
 *
 * - Provides repository functionality.
 * - Handles data persistence operations.
 */
interface RepositoryInterface
{
    /**
     * Specification:
     *
     * - Save an entity to the database.
     * - Return the saved entity.
     */
    public function save(array $data): array;

    // Method without docblock - should trigger error when matching methodPatterns
    public function delete(int $id): void;

    /**
     * Specification:
     *
     * - Find an entity by ID.
     * - Return null if not found.
     */
    public function findById(int $id): ?array;
}


<?php

declare(strict_types=1);

namespace App\Capability;

class InstanceofAndCatch
{
    public function checkInstanceof($value): bool
    {
        // This should be caught when checkFqcn is enabled with 'instanceof' reference type
        return $value instanceof \DateTime;
    }

    public function checkImmutableInstanceof($value): bool
    {
        // This should be caught when checkFqcn is enabled with 'instanceof' reference type
        return $value instanceof \DateTimeImmutable;
    }

    public function handleException(): void
    {
        try {
            throw new \Exception('test');
        // This should be caught when checkFqcn is enabled with 'catch' reference type
        } catch (\Exception $e) {
            // Handle exception
        }
    }

    public function handleMultipleExceptions(): void
    {
        try {
            throw new \Exception('test');
        // Both should be caught when checkFqcn is enabled with 'catch' reference type
        } catch (\RuntimeException | \LogicException $e) {
            // Handle exceptions
        }
    }
}


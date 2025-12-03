<?php

declare(strict_types=1);

namespace App\Capability;

class NewInstantiation
{
    public function createDate()
    {
        // This should be caught when checkFqcn is enabled with 'new' reference type
        return new \DateTime('now');
    }

    public function createImmutableDate()
    {
        // This should be caught when checkFqcn is enabled with 'new' reference type
        return new \DateTimeImmutable('now');
    }

    public function createAllowedClass()
    {
        // This should not be caught
        return new \stdClass();
    }
}


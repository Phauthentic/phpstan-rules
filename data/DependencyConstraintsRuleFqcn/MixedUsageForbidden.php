<?php

declare(strict_types=1);

namespace App\Capability;

// This file mixes use statements with FQCN for forbidden classes
// Both should be caught when enabled

use DateTime;
use DateTimeImmutable;

class MixedUsageForbidden
{
    // Caught with use statement checking
    public function useStatement(): DateTime
    {
        return new DateTime('now');
    }

    // Caught with FQCN checking when enabled
    public function fqcnInstantiation()
    {
        return new \DateTime('now');
    }

    // Caught with use statement checking
    public function useStatementImmutable(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    // Caught with FQCN checking when enabled
    public function fqcnImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}


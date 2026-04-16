<?php

declare(strict_types=1);

namespace App\ForbiddenDateTimeComparison;

use DateTimeInterface;

final class ScopedViolations
{
    public function matchedMethod(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return $a === $b;
    }

    public function unmatchedMethod(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return $a === $b;
    }
}

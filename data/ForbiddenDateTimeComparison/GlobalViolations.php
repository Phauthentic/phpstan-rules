<?php

declare(strict_types=1);

namespace App\ForbiddenDateTimeComparison;

use DateTimeInterface;

final class GlobalViolations
{
    public function identicalDates(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return $a === $b;
    }

    public function notIdenticalDates(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return $a !== $b;
    }

    public function looseOk(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return $a == $b;
    }

    public function mixedWithObject(object $a, DateTimeInterface $b): bool
    {
        return $a === $b;
    }
}

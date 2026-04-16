<?php

declare(strict_types=1);

namespace App\ForbiddenDateTimeComparison;

use DateTimeInterface;

function global_datetime_compare(DateTimeInterface $left, DateTimeInterface $right): bool
{
    return $left === $right;
}

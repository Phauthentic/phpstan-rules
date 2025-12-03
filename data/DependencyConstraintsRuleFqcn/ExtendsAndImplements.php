<?php

declare(strict_types=1);

namespace App\Capability;

// This should be caught when checkFqcn is enabled with 'extends' reference type
class CustomDateTime extends \DateTime
{
}

// This should be caught when checkFqcn is enabled with 'implements' reference type
class CustomDateTimeInterface implements \DateTimeInterface
{
    public function format(string $format): string
    {
        return '';
    }

    public function getTimezone(): \DateTimeZone|false
    {
        return false;
    }

    public function getOffset(): int
    {
        return 0;
    }

    public function getTimestamp(): int
    {
        return 0;
    }

    public function diff(\DateTimeInterface $targetObject, bool $absolute = false): \DateInterval
    {
        return new \DateInterval('P0D');
    }

    public function __wakeup(): void
    {
    }
}

// This should not be caught (allowed class)
class CustomException extends \Exception
{
}


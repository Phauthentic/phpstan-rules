<?php

declare(strict_types=1);

namespace App\Capability;

class TypeHints
{
    // This should be caught when checkFqcn is enabled with 'property' reference type
    private \DateTime $dateTime;

    private \DateTimeImmutable $immutableDateTime;

    private \stdClass $allowed;

    // This should be caught when checkFqcn is enabled with 'param' reference type
    public function setDateTime(\DateTime $date): void
    {
        $this->dateTime = $date;
    }

    // This should be caught when checkFqcn is enabled with 'param' reference type
    public function setImmutableDateTime(\DateTimeImmutable $date): void
    {
        $this->immutableDateTime = $date;
    }

    // This should be caught when checkFqcn is enabled with 'return' reference type
    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    // This should be caught when checkFqcn is enabled with 'return' reference type
    public function getImmutableDateTime(): \DateTimeImmutable
    {
        return $this->immutableDateTime;
    }

    public function getAllowed(): \stdClass
    {
        return $this->allowed;
    }

    // Test nullable types
    public function getNullableDateTime(): ?\DateTime
    {
        return null;
    }

    // Test union types (PHP 8.0+)
    public function getUnionType(): \DateTime|\DateTimeImmutable
    {
        return $this->dateTime;
    }
}


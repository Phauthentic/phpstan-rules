<?php

declare(strict_types=1);

namespace App\Capability;

class SelectiveReferenceTypes
{
    // Property type hint
    private \DateTime $property;

    // Constructor with param type hint
    public function __construct(\DateTime $param)
    {
        $this->property = $param;
    }

    // Return type hint
    public function getDate(): \DateTime
    {
        return $this->property;
    }

    // New instantiation
    public function createNew()
    {
        return new \DateTime('now');
    }

    // Static call
    public function createFromFormat()
    {
        return \DateTime::createFromFormat('Y-m-d', '2023-01-01');
    }

    // Class constant
    public function getClassName()
    {
        return \DateTime::class;
    }

    // instanceof
    public function checkType($value): bool
    {
        return $value instanceof \DateTime;
    }
}


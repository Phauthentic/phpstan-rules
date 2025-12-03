<?php

declare(strict_types=1);

namespace App\Capability;

class StaticCalls
{
    public function createFromFormat()
    {
        // This should be caught when checkFqcn is enabled with 'static_call' reference type
        return \DateTime::createFromFormat('Y-m-d', '2023-01-01');
    }

    public function createImmutableFromFormat()
    {
        // This should be caught when checkFqcn is enabled with 'static_call' reference type
        return \DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-01');
    }

    public function getAtomConstant()
    {
        // This should be caught when checkFqcn is enabled with 'static_property' reference type
        return \DateTime::ATOM;
    }

    public function getClassConstant()
    {
        // This should be caught when checkFqcn is enabled with 'class_const' reference type
        return \DateTime::class;
    }

    public function getImmutableClassConstant()
    {
        // This should be caught when checkFqcn is enabled with 'class_const' reference type
        return \DateTimeImmutable::class;
    }

    public function getAllowedClassConstant()
    {
        // This should not be caught
        return \stdClass::class;
    }
}


<?php

namespace App\BoolNaming;

class EdgeCaseMethodBoolClass
{
    public function __construct()
    {
    }

    public function __toString(): string
    {
        return 'test';
    }

    public function noReturnType()
    {
        return true;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function check(): bool
    {
        return true;
    }
}

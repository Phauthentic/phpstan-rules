<?php

namespace App;

class TooManyArgumentsClass
{
    public function methodWithTooManyArguments(int $arg1, int $arg2, int $arg3, int $arg4): void
    {
        // Method implementation
    }

    public function validMethod(int $arg1, int $arg2): void
    {
        // Valid method with acceptable number of arguments
    }
}

namespace App\Service;

class TooManyArgsService
{
    public function methodWithTooManyArguments(int $a, int $b, int $c, int $d, int $e): void
    {
        // This should trigger error when pattern matches Service
    }
}

namespace App\Other;

class TooManyArgsOther
{
    public function methodWithTooManyArguments(int $a, int $b, int $c, int $d, int $e): void
    {
        // This should NOT trigger error when pattern doesn't match
    }
}

<?php

declare(strict_types=1);

namespace Phauthentic\PHPStanRules\Tests\Data\MethodSignatureMustMatch;

// This class is missing the required execute method
class MyTestController
{
    public function index(): void
    {
    }
}

// This class implements the required method correctly
class AnotherTestController
{
    public function execute(int $id): void
    {
    }
}

// This class is missing the required method
class YetAnotherTestController
{
    public function something(): void
    {
    }
}

// This class should not be affected (doesn't match pattern)
class NotAController
{
    public function execute(int $id): void
    {
    }
}


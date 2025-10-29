<?php

declare(strict_types=1);

namespace App\SpecificationDocblock;

class InvalidMethodDocblockClass
{
    /**
     * Specification:
     * This is missing the blank line and list items.
     */
    public function testMethod(): void
    {
    }

    public function otherMethod(): void
    {
    }
}


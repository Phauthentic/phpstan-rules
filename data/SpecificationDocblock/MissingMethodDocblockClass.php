<?php

declare(strict_types=1);

namespace App\SpecificationDocblock;

class MissingMethodDocblockClass
{
    public function testMethod(): void
    {
    }

    public function otherMethod(): void
    {
    }
}


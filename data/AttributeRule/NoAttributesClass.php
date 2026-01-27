<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Class without any attributes - should always pass.
 */
class NoAttributesClass
{
    private string $property;

    public function doSomething(): void
    {
    }
}

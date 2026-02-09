<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Application;

use DateTime;

class NonModularImport
{
    public function getDate(): DateTime
    {
        return new DateTime();
    }
}

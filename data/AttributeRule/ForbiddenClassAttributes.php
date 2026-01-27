<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Deprecated;

/**
 * Class with forbidden attributes - should fail.
 */
#[Deprecated]
class ForbiddenClassAttributes
{
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\CustomAttribute;

/**
 * Class with attributes not in allowed list - should fail.
 */
#[CustomAttribute]
class NotAllowedClassAttributes
{
}

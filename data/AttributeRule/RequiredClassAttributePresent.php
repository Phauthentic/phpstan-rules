<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class with required attribute present - should pass.
 */
#[Route('/api')]
class RequiredClassAttributePresent
{
}

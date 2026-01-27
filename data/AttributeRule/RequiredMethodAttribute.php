<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class with methods for testing required attributes.
 */
class RequiredMethodAttribute
{
    /**
     * Method with required attribute present - should pass.
     */
    #[Route('/users')]
    public function withRequiredAction(): void
    {
    }

    /**
     * Method missing required attribute - should fail.
     */
    public function missingRequiredAction(): void
    {
    }
}

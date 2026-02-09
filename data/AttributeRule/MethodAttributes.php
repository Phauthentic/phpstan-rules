<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Attribute\Deprecated;
use App\Attribute\CustomAttribute;

/**
 * Class with method attributes for testing.
 */
class MethodAttributes
{
    /**
     * Method with allowed attribute - should pass.
     */
    #[Route('/users')]
    public function allowedAction(): void
    {
    }

    /**
     * Method with forbidden attribute - should fail.
     */
    #[Deprecated]
    public function forbiddenAction(): void
    {
    }

    /**
     * Method with attribute not in allowed list - should fail.
     */
    #[CustomAttribute]
    public function notAllowedAction(): void
    {
    }
}

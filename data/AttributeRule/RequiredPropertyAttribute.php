<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;

/**
 * Class with properties for testing required attributes.
 */
class RequiredPropertyAttribute
{
    /**
     * Property with required attribute present - should pass.
     */
    #[Column]
    private string $withRequired;

    /**
     * Property missing required attribute - should fail.
     */
    private string $missingRequired;
}

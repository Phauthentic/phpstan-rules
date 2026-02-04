<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Attribute\FrameworkAttribute;
use App\Attribute\DomainAttribute;

/**
 * Entity in Domain layer - framework attributes should be forbidden on properties.
 */
class CombinedClassPropertyPattern
{
    /**
     * Property with forbidden framework attribute in domain - should fail.
     */
    #[FrameworkAttribute]
    private string $forbiddenProperty;

    /**
     * Property with allowed domain attribute - should pass.
     */
    #[DomainAttribute]
    private string $allowedProperty;
}

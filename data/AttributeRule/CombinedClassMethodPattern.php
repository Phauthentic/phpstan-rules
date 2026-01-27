<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Attribute\FrameworkAttribute;
use App\Attribute\DomainAttribute;

/**
 * Class in Domain layer - framework attributes should be forbidden on methods.
 */
class CombinedClassMethodPattern
{
    /**
     * Method with forbidden framework attribute in domain - should fail.
     */
    #[FrameworkAttribute]
    public function processData(): void
    {
    }

    /**
     * Method with allowed domain attribute - should pass.
     */
    #[DomainAttribute]
    public function handleDomain(): void
    {
    }
}

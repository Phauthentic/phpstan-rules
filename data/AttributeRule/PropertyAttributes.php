<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use App\Attribute\Deprecated;
use App\Attribute\CustomAttribute;

/**
 * Class with property attributes for testing.
 */
class PropertyAttributes
{
    /**
     * Property with allowed attribute - should pass.
     */
    #[Id]
    #[Column]
    private int $id;

    /**
     * Property with forbidden attribute - should fail.
     */
    #[Deprecated]
    private string $forbiddenProperty;

    /**
     * Property with attribute not in allowed list - should fail.
     */
    #[CustomAttribute]
    private string $notAllowedProperty;
}

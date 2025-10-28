<?php

declare(strict_types=1);

namespace App\Capability\ProductCatalog\Domain\Model;

/**
 * Valid domain entity
 */
class Product
{
    public function __construct(
        private string $id,
        private string $name
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}


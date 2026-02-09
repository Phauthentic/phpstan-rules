<?php

namespace App\Domain;

$entity = new class {
    public function getName(): string
    {
        return 'name';
    }

    public function setName(string $name): void
    {
    }
};

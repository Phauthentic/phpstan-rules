<?php

declare(strict_types=1);

namespace App\Forbidden;

class ChildService extends ForbiddenService
{
    public function callParent(): string
    {
        return parent::create();
    }
}

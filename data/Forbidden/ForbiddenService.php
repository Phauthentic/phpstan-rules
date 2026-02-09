<?php

declare(strict_types=1);

namespace App\Forbidden;

class ForbiddenService
{
    public static function create(): string
    {
        return 'created';
    }

    public function callSelf(): string
    {
        return self::create();
    }

    public function callStatic(): string
    {
        return static::create();
    }
}

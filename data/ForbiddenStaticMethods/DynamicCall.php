<?php

declare(strict_types=1);

namespace App\Service;

class DynamicCallService
{
    public function dynamicMethod(): void
    {
        $method = 'calculate';
        \App\Utils\StaticHelper::$method();
    }

    public function dynamicClass(): void
    {
        $class = \App\Utils\StaticHelper::class;
        $class::calculate();
    }
}

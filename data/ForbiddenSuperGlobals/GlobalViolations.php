<?php

declare(strict_types=1);

namespace App\ForbiddenSuperGlobals;

final class GlobalViolations
{
    public function usesGet(): void
    {
        $x = $_GET;
    }

    public function usesPost(): void
    {
        $y = $_POST;
    }
}

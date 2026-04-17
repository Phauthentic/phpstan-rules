<?php

declare(strict_types=1);

namespace App\ForbiddenSuperGlobals;

final class ScopedViolations
{
    public function matchedMethod(): void
    {
        $a = $_GET;
    }

    public function otherMethod(): void
    {
        $b = $_POST;
    }
}

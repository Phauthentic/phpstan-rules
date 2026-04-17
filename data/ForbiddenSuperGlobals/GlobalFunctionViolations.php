<?php

declare(strict_types=1);

namespace App\ForbiddenSuperGlobals;

function global_uses_server(): void
{
    $z = $_SERVER;
}

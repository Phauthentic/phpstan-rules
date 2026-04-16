<?php

declare(strict_types=1);

namespace App\ForbiddenBusinessLogicRule;

final class EmptyGlobalFixture
{
    public function onlyIf(): void
    {
        if (true) {
        }
    }

    public function unmatchedHasIf(): void
    {
        if (true) {
        }
    }
}

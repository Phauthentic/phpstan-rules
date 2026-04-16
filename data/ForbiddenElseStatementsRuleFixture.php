<?php

declare(strict_types=1);

namespace App\ElseRules;

class Matched
{
    public function matchedMethod(bool $x): void
    {
        if ($x) {
            return;
        } else {
            return;
        }
    }

    public function anotherMatched(bool $x): void
    {
        if ($x) {
            return;
        } else {
            return;
        }
    }
}

class Unmatched
{
    public function any(bool $x): void
    {
        if ($x) {
            return;
        } else {
            return;
        }
    }
}

<?php

namespace App;

class MaxLineLengthTestClass
{
    public function methodWithVeryLongLineThatExceedsTheMaximumAllowedLength(): void
    {
        // This line is intentionally longer than 80 characters to test the rule
    }
}

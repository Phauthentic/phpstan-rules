<?php

namespace App;

class MaxLineLengthExcludedClass
{
    public function methodWithVeryLongLineThatShouldBeExcludedFromTheRule(): void
    {
        // This line is intentionally longer than 80 characters but should be excluded
    }
}

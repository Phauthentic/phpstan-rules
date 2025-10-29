<?php

namespace App;

class MaxLineLengthMultipleLines // This line is exactly 95 characters long to test multiple violations in one file
{
    public function methodWithMultipleVeryLongLinesThatExceedTheMaximumAllowedLengthOfEightyCharacters(): void
    {
        $variable = 'This is a short line';
        // All good here
    }
}


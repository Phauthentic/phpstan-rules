<?php

namespace App;

/**
 * This is a very long docblock comment that definitely exceeds the eighty character maximum line length limit.
 * And here is another very long line in the docblock that also exceeds the eighty character limit significantly.
 */
class MaxLineLengthLongDocBlockClass
{
    public function shortMethod(): void
    {
        $variable = 'short';
    }

    /**
     * This method has a very long docblock description that definitely exceeds the eighty character maximum line length.
     */
    public function methodWithVeryLongLineInsideThatDefinitelyExceedsTheEightyCharacterMaximumLineLengthLimit(): void
    {
        $thisVariableNameIsAlsoExtremelyLongAndWillDefinitelyExceedTheMaximumLineLengthLimitOf80Characters = true;
    }
}


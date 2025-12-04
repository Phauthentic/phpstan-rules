<?php

namespace App;

use Some\Very\Long\Namespace\That\Definitely\Exceeds\Eighty\Characters\SomeVeryLongClassName;
use Another\Very\Long\Namespace\That\Also\Exceeds\The\Maximum\Line\Length\Limit\AnotherClass;
use YetAnother\Very\Long\Namespace\Path\That\Goes\On\And\On\Until\It\Exceeds\EightyCharacters;

class MaxLineLengthLongUseStatementsClass
{
    public function shortMethod(): void
    {
        $variable = 'short';
    }

    public function methodWithVeryLongLineInsideThatDefinitelyExceedsTheEightyCharacterMaximumLineLengthLimit(): void
    {
        $thisVariableNameIsAlsoExtremelyLongAndWillDefinitelyExceedTheMaximumLineLengthLimitOf80Characters = true;
    }
}


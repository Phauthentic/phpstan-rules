<?php

namespace App\Test;

class InvalidMissingInheritDocClass
{
    /**
     * This method has a docblock but no @inheritDoc
     *
     * @return void
     */
    public function methodWithoutInheritDoc(): void
    {
    }

    /**
     * @return void
     */
    public function anotherMethodWithoutInheritDoc(): void
    {
    }
}


<?php

namespace App\Test;

class ValidInheritDocClass
{
    /**
     * @inheritDoc
     */
    public function methodWithInheritDoc(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function methodWithInheritdoc(): void
    {
    }

    /**
     * Some description text
     *
     * @inheritDoc
     * @return void
     */
    public function methodWithInheritDocAndOtherTags(): void
    {
    }

    /**
     * @inheritDoc Some additional text after
     */
    public function methodWithInheritDocAndText(): void
    {
    }
}


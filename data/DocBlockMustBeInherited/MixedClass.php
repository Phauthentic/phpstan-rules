<?php

namespace App\Test;

class MixedClass
{
    /**
     * This method matches the pattern and has @inheritDoc - VALID
     *
     * @inheritDoc
     */
    public function matchingMethodValid(): void
    {
    }

    /**
     * This method matches the pattern but no @inheritDoc - INVALID
     *
     * @return void
     */
    public function matchingMethodInvalid(): void
    {
    }

    // This method matches the pattern but has no docblock - INVALID
    public function matchingMethodNoDocblock(): void
    {
    }

    /**
     * This method does NOT match the pattern, so it should be ignored
     *
     * @return void
     */
    public function nonMatchingMethod(): void
    {
    }
}


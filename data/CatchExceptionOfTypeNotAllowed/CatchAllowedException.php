<?php

declare(strict_types=1);

namespace App\Service;

class TestService
{
    public function methodWithAllowedCatch(): void
    {
        try {
            // Some code that might throw an exception
        } catch (\InvalidArgumentException $e) {
            // This should NOT trigger an error (not in forbidden list)
        }

        try {
            // Some code that might throw an exception
        } catch (\RuntimeException $e) {
            // This should NOT trigger an error (not in forbidden list)
        }

        try {
            // Some code that might throw an exception
        } catch (\DomainException $e) {
            // This should NOT trigger an error (not in forbidden list)
        }
    }
} 
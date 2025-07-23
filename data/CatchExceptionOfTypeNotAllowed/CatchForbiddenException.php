<?php

declare(strict_types=1);

namespace App\Service;

class TestService
{
    public function methodWithForbiddenCatch(): void
    {
        try {
            // Some code that might throw an exception
        } catch (\Exception $e) {
            // This should trigger an error
        }

        try {
            // Some code that might throw an error
        } catch (\Error $e) {
            // This should trigger an error
        }

        try {
            // Some code that might throw a throwable
        } catch (\Throwable $e) {
            // This should trigger an error
        }

        try {
            // Some code that might throw a specific exception
        } catch (\InvalidArgumentException $e) {
            // This should NOT trigger an error (not in forbidden list)
        }
    }
} 
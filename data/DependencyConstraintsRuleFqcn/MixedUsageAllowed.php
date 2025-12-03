<?php

declare(strict_types=1);

namespace App\Capability;

// This file uses allowed classes both with use statements and FQCN
// None of these should trigger errors

use Exception;
use stdClass;

class MixedUsageAllowed
{
    private stdClass $obj;

    public function useStatement(): Exception
    {
        return new Exception('test');
    }

    public function fqcn(): \stdClass
    {
        return new \stdClass();
    }

    public function catchException(): void
    {
        try {
            throw new \Exception('test');
        } catch (\Exception $e) {
            // Handle
        }
    }
}


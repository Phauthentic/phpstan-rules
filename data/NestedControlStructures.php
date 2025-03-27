<?php

namespace App;

class NestedControlStructures
{
    public function nestedIfStatements(): void
    {
        if (true) {
            if (true) {
                if (true) {
                    // This should trigger the rule
                }
            }
        }
    }

    public function nestedTryCatchStatements(): void
    {
        try {
            try {
                try {
                    // This should trigger the rule
                } catch (\Exception $e) {
                    // Handle exception
                }
            } catch (\Exception $e) {
                // Handle exception
            }
        } catch (\Exception $e) {
            // Handle exception
        }
    }

    public function mixedIfAndCatch(): void
    {
        if (false) {
            try {
                if (true) {
                    // This should trigger the rule
                }
            } catch (\Exception $e) {
                // Handle exception
            }
        } else {
            try {
                if (true) {
                    // This should trigger the rule
                }
            } catch (\Exception $e) {
                // Handle exception
            }
        }
    }
}

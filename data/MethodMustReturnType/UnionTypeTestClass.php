<?php

class UnionTypeTestClass
{
    // Test cases for oneOf functionality
    public function validOneOfInt(): int { return 1; }
    public function validOneOfString(): string { return 'test'; }
    public function validOneOfBool(): bool { return true; }
    
    // Test cases for allOf functionality (simplified)
    public function validAllOfInt(): int { return 1; }
    public function validAllOfString(): string { return 'test'; }
    
    // Invalid cases
    public function invalidOneOf(): float { return 1.0; }
    public function invalidAllOf(): bool { return true; }
} 
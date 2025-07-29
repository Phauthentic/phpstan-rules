<?php

class AnyOfTestClass
{
    // Valid cases
    public function validObject(): SomeObject { return new SomeObject(); }
    public function validVoid(): void { return; }
    
    // Invalid cases
    public function invalidType(): int { return 1; }
}

class SomeObject {} 
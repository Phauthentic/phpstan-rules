<?php

class ReturnTypeTestClass
{
    // Invalid cases (should trigger errors)
    public function mustReturnInt(): void { return; }
    public function mustReturnNullableString(): string { return 'test'; }
    public function mustReturnVoid(): int { return 1; }
    public function mustReturnVoidLegacy(): int { return 1; }
    public function mustReturnSpecificObject(): OtherObject { return new OtherObject(); }
    public function mustReturnOneOf(): float { return 1.0; }
    public function mustReturnAllOf(): int { return 1; }
    public function mustReturnOneOfNullable(): string { return 'test'; }
    public function mustReturnNullableObject(): SomeObject { return new SomeObject(); }
}

class SomeObject {}
class OtherObject {}

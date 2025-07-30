<?php

class RegexTestClass
{
    // Valid cases for regex patterns
    public function validUserEntity(): UserEntity { return new UserEntity(); }
    public function validProductEntity(): ProductEntity { return new ProductEntity(); }
    public function validVoid(): void { return; }
    public function validInt(): int { return 1; }
    
    // Invalid cases
    public function invalidOtherClass(): OtherClass { return new OtherClass(); }
    public function invalidString(): string { return 'test'; }
}

class UserEntity {}
class ProductEntity {}
class OtherClass {} 
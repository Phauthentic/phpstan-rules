<?php

class RegexAllOfTestClass
{
    // Valid cases for allOf with regex patterns
    public function validUnionWithUser(): UserEntity|int { return new UserEntity(); }
    public function validUnionWithProduct(): ProductEntity|string { return 'test'; }
    
    // Invalid cases
    public function invalidUnionMissingUser(): OtherClass|int { return 1; }
    public function invalidUnionMissingProduct(): UserEntity|OtherClass { return new UserEntity(); }
}

class UserEntity {}
class ProductEntity {}
class OtherClass {} 
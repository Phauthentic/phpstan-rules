<?php

class EntityRegexTestClass
{
    // Valid cases - these should match the regex pattern
    public function getUser(): UserEntity { return new UserEntity(); }
    public function getProduct(): ProductEntity { return new ProductEntity(); }
    public function getOrder(): OrderEntity { return new OrderEntity(); }
    public function getVoid(): void { return; }
    
    // Invalid cases - these should not match
    public function getOther(): OtherClass { return new OtherClass(); }
    public function getString(): string { return 'test'; }
}

class UserEntity {}
class ProductEntity {}
class OrderEntity {}
class OtherClass {} 
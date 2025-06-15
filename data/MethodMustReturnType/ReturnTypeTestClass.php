<?php

class ReturnTypeTestClass
{
    public function mustReturnInt(): void {}
    public function mustReturnNullableString(): string {}
    public function mustReturnVoid(): int {}
    public function mustReturnSpecificObject(): OtherObject {}
}

class SomeObject {}
class OtherObject {}

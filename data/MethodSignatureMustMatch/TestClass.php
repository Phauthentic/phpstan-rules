<?php

class DummyClass
{
}

class TestClass
{
    public function testMethod(int $a)
    {
    }

    public function testMethodNoType($x, string $y)
    {
    }

    public function testMethodWithWrongType(int $x, int $y)
    {
    }

    // Test max parameters violation
    public function testMaxParams(int $a, string $b, int $c, string $d)
    {
    }

    // Test parameter name pattern mismatch
    public function testNameMismatch(int $wrongName, string $anotherWrong)
    {
    }

    // Test nullable types
    public function testNullableTypes(?int $nullableInt, ?string $nullableString)
    {
    }

    // Test class types
    public function testClassTypes(DummyClass $dummy, string $name)
    {
    }

    // Test protected visibility
    protected function testProtectedMethod(int $value)
    {
    }

    // Test method without visibility requirement (should pass)
    public function testNoVisibilityReq(int $x)
    {
    }

    // Test valid method matching all criteria
    public function testValidMethod(int $alpha, string $beta)
    {
    }
}

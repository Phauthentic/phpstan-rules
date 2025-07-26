<?php

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
}

<?php

class Facade
{
    public function someMethod(): SomeObject { return new SomeObject(); }
    public function anotherMethod(): void { return; }
    public function invalidMethod(): int { return 1; }
}

class SomeObject {} 
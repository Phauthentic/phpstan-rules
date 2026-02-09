<?php

namespace App\Domain;

class UserEntity
{
    private string $name;
    private int $age;
    private bool $active;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    protected function getActive(): bool
    {
        return $this->active;
    }

    protected function setActive(bool $active): void
    {
        $this->active = $active;
    }

    private function getPrivateValue(): string
    {
        return 'private';
    }

    private function setPrivateValue(string $value): void
    {
        // This should not trigger an error (private)
    }

    public function doSomething(): void
    {
        // Regular method, should not trigger
    }

    public function get(): void
    {
        // Should not trigger - no uppercase letter after 'get'
    }

    public function set(): void
    {
        // Should not trigger - no uppercase letter after 'set'
    }
}

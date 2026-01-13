<?php

namespace App\Service;

class ServiceWithAccessors
{
    private string $config;

    public function getConfig(): string
    {
        return $this->config;
    }

    public function setConfig(string $config): void
    {
        $this->config = $config;
    }
}

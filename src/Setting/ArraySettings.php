<?php

declare(strict_types=1);

namespace Szabmik\Slim\Setting;

abstract class ArraySettings implements SettingsInterface
{
    protected array $settings = [];

    public function get(string $key): mixed
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    public function getAll(): array
    {
        return $this->settings;
    }

    public function has(string $key): bool
    {
        return isset($this->settings[$key]);
    }
}

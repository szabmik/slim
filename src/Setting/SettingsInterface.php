<?php

declare(strict_types=1);

namespace Szabmik\Slim\Setting;

interface SettingsInterface
{
    public function get(string $key): mixed;
    public function getAll(): array;
    public function has(string $key): bool;
}

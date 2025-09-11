<?php

declare(strict_types=1);

namespace Szabmik\Slim\Setting;

/**
 * Abstract base class for storing application settings in an array.
 *
 * Provides a simple in-memory key-value store implementation of the SettingsInterface.
 * Can be extended by concrete classes to initialize specific configuration values.
 */
abstract class ArraySettings implements SettingsInterface
{
    /**
     * @var array<string, mixed> The array holding all configuration key-value pairs.
     */
    protected array $settings = [];

    /**
     * Retrieves the value associated with the given configuration key.
     *
     * @param string $key The name of the setting to retrieve.
     *
     * @return mixed|null The value if it exists, or null otherwise.
     */
    public function get(string $key): mixed
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    /**
     * Returns all available configuration settings.
     *
     * @return array<string, mixed> The full settings array.
     */
    public function getAll(): array
    {
        return $this->settings;
    }

    /**
     * Determines whether a given configuration key exists.
     *
     * @param string $key The name of the setting to check.
     *
     * @return bool True if the setting exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return isset($this->settings[$key]);
    }
}

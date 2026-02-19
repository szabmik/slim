<?php

declare(strict_types=1);

namespace Szabmik\Slim\Settings;

/**
 * Interface for accessing application settings.
 *
 * Provides a unified contract for retrieving configuration values
 * used throughout the application, supporting both individual keys
 * and full configuration sets.
 */
interface SettingsInterface
{
    /**
     * Retrieves the value associated with a specific configuration key.
     *
     * @param string $key The key identifying the setting.
     *
     * @return mixed The value associated with the key, or null if not found.
     */
    public function get(string $key): mixed;

    /**
     * Returns all configuration key-value pairs.
     *
     * @return array An associative array of all settings.
     */
    public function getAll(): array;

    /**
     * Checks whether a specific configuration key exists.
     *
     * @param string $key The key to check for existence.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;
}

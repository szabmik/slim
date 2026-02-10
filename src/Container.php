<?php

declare(strict_types=1);

namespace Szabmik\Slim;

use DI\ContainerBuilder;

/**
 * A helper class for creating a configured PHP-DI Container instance.
 *
 * This class provides a static method for initializing a DI\Container
 * with optional compilation and dynamic definition sources (e.g. configuration arrays or callables).
 * Useful for modular and testable dependency injection setup.
 */
class Container
{
    /**
     * Creates and configures a PHP-DI Container.
     *
     * @param array<string, mixed>|callable|array<callable>|null $definitionSource
     *        - If array of definitions: added directly to container
     *        - If callable: invoked with ContainerBuilder as parameter
     *        - If array of callables: each callable is invoked with ContainerBuilder
     * @param bool $enableCompilation Whether to enable container compilation (for performance).
     *
     * @return \DI\Container The configured dependency injection container.
     */
    public static function create(
        array|null|callable $definitionSource = null,
        bool $enableCompilation = false
    ): \DI\Container {
        $containerBuilder = new ContainerBuilder();

        if ($enableCompilation) {
            $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
        }

        if ($definitionSource !== null) {
            self::addDefinitions($containerBuilder, $definitionSource);
        }

        return $containerBuilder->build();
    }

    /**
     * Adds definitions to the container builder.
     *
     * Supports three formats:
     * 1. Callable - invoked with builder as parameter for custom configuration
     * 2. Array of key-value definitions - added as DI definitions
     * 3. Array of callables - each callable is invoked with builder
     *
     * @param ContainerBuilder $builder The container builder to configure.
     * @param array<string, mixed>|callable|array<callable> $definitionSource The definitions to add.
     */
    private static function addDefinitions(ContainerBuilder $builder, array|callable $definitionSource): void
    {
        // Single callable: invoke with builder
        if (is_callable($definitionSource)) {
            $definitionSource($builder);
            return;
        }

        // Array: check if it's array of callables or array of definitions
        if (self::isArrayOfCallables($definitionSource)) {
            // Array of callables: invoke each
            foreach ($definitionSource as $callable) {
                $callable($builder);
            }
        } else {
            // Array of definitions: add directly
            $builder->addDefinitions($definitionSource);
        }
    }

    /**
     * Checks if the given array contains only callable values.
     *
     * @param array<mixed> $array The array to check.
     *
     * @return bool True if all values are callable, false otherwise.
     */
    private static function isArrayOfCallables(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        foreach ($array as $item) {
            if (!is_callable($item)) {
                return false;
            }
        }

        return true;
    }
}

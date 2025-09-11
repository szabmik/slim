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
     * @param array|callable|null $definitionSource Optional definitions or callables to modify the ContainerBuilder.
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

        if (!is_null($definitionSource)) {
            $sources = is_array($definitionSource) ? $definitionSource : [$definitionSource];

            foreach ($sources as $source) {
                if (is_callable($source)) {
                    call_user_func($source, $containerBuilder);
                }
            }
        }

        return $containerBuilder->build();
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim;

use DI\ContainerBuilder;

class Container
{
    public static function create(
        array|null|callable $definitionSource = null,
        $enableCompilation = false
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

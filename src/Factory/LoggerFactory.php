<?php

declare(strict_types=1);

namespace Szabmik\Slim\Factory;

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Szabmik\Slim\Settings\LoggerSettings;

class LoggerFactory
{
    public static function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions([
            LoggerInterface::class => function (ContainerInterface $c) {
                $loggerSettings = $c->has(LoggerSettings::class)
                    ? $c->get(LoggerSettings::class)
                    : new LoggerSettings('app', 'php://stdout', Level::Debug);

                $logger = new Logger($loggerSettings->getName());

                $processor = new UidProcessor();
                $logger->pushProcessor($processor);

                $handler = new StreamHandler(
                    $loggerSettings->getStream(),
                    $loggerSettings->getLevel()
                );

                $logger->pushHandler($handler);

                return $logger;
            }
        ]);
    }
}
<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Factory;

use DI\ContainerBuilder;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Szabmik\Slim\Factory\LoggerFactory;
use Szabmik\Slim\Settings\LoggerSettings;

class LoggerFactoryTest extends TestCase
{
    public function testRegistersLoggerInterfaceDefinition(): void
    {
        $builder = new ContainerBuilder();
        LoggerFactory::register($builder);

        $container = $builder->build();

        $this->assertTrue($container->has(LoggerInterface::class));
    }

    public function testCreatesMonologLoggerInstance(): void
    {
        $builder = new ContainerBuilder();
        LoggerFactory::register($builder);

        $container = $builder->build();
        $logger = $container->get(LoggerInterface::class);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testDefaultLoggerNameIsApp(): void
    {
        $builder = new ContainerBuilder();
        LoggerFactory::register($builder);

        $container = $builder->build();
        /** @var Logger $logger */
        $logger = $container->get(LoggerInterface::class);

        $this->assertSame('app', $logger->getName());
    }

    public function testLoggerHasUidProcessor(): void
    {
        $builder = new ContainerBuilder();
        LoggerFactory::register($builder);

        $container = $builder->build();
        /** @var Logger $logger */
        $logger = $container->get(LoggerInterface::class);

        $processors = $logger->getProcessors();
        $uidProcessors = array_filter($processors, fn($p) => $p instanceof UidProcessor);

        $this->assertNotEmpty($uidProcessors);
    }

    public function testUsesCustomLoggerSettingsWhenRegistered(): void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            LoggerSettings::class => new LoggerSettings('custom-logger', 'php://stderr', Level::Warning),
        ]);

        LoggerFactory::register($builder);

        $container = $builder->build();
        /** @var Logger $logger */
        $logger = $container->get(LoggerInterface::class);

        $this->assertSame('custom-logger', $logger->getName());
    }

    public function testFallsBackToDefaultsWhenLoggerSettingsNotRegistered(): void
    {
        $builder = new ContainerBuilder();
        LoggerFactory::register($builder);

        $container = $builder->build();
        /** @var Logger $logger */
        $logger = $container->get(LoggerInterface::class);

        $this->assertSame('app', $logger->getName());
        $this->assertNotEmpty($logger->getHandlers());
    }
}

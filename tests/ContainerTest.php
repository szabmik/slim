<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Szabmik\Slim\Container;

class ContainerTest extends TestCase
{
    public function testCreateReturnsContainerInstance(): void
    {
        $container = Container::create();
        $this->assertInstanceOf(\DI\Container::class, $container);
    }

    public function testCreateWithNullDefinitionSource(): void
    {
        $container = Container::create(null);
        $this->assertInstanceOf(\DI\Container::class, $container);
    }

    public function testCreateWithArrayDefinitions(): void
    {
        $container = Container::create([
            'test.key' => 'test-value',
        ]);

        $this->assertSame('test-value', $container->get('test.key'));
    }

    public function testCreateWithCallableDefinition(): void
    {
        $container = Container::create(function (ContainerBuilder $builder) {
            $builder->addDefinitions([
                'callable.key' => 'callable-value',
            ]);
        });

        $this->assertSame('callable-value', $container->get('callable.key'));
    }

    public function testCreateWithArrayOfCallables(): void
    {
        $callable1 = function (ContainerBuilder $builder) {
            $builder->addDefinitions(['key1' => 'value1']);
        };

        $callable2 = function (ContainerBuilder $builder) {
            $builder->addDefinitions(['key2' => 'value2']);
        };

        $container = Container::create([$callable1, $callable2]);

        $this->assertSame('value1', $container->get('key1'));
        $this->assertSame('value2', $container->get('key2'));
    }

    public function testCreateRegistersDefaultLogger(): void
    {
        $container = Container::create();
        $this->assertTrue($container->has(LoggerInterface::class));
    }

    public function testCreateWithoutDefaultLogger(): void
    {
        $container = Container::create(null, false, false);
        $this->assertFalse($container->has(LoggerInterface::class));
    }

    public function testCreateWithDefinitionsAndDefaultLogger(): void
    {
        $container = Container::create([
            'app.name' => 'test-app',
        ]);

        $this->assertSame('test-app', $container->get('app.name'));
        $this->assertTrue($container->has(LoggerInterface::class));
    }
}

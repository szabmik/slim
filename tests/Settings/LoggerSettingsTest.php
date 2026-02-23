<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Settings;

use Monolog\Level;
use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Settings\ArraySettings;
use Szabmik\Slim\Settings\LoggerSettings;
use Szabmik\Slim\Settings\ISettings;

class LoggerSettingsTest extends TestCase
{
    public function testImplementsSettingsInterface(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $this->assertInstanceOf(ISettings::class, $settings);
    }

    public function testExtendsArraySettings(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $this->assertInstanceOf(ArraySettings::class, $settings);
    }

    public function testGetName(): void
    {
        $settings = new LoggerSettings('my-logger', 'php://stderr', Level::Info);
        $this->assertSame('my-logger', $settings->getName());
    }

    public function testGetStream(): void
    {
        $settings = new LoggerSettings('app', '/var/log/app.log', Level::Warning);
        $this->assertSame('/var/log/app.log', $settings->getStream());
    }

    public function testGetLevel(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Error);
        $this->assertSame(Level::Error, $settings->getLevel());
    }

    public function testGetAllReturnsAllSettings(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $all = $settings->getAll();

        $this->assertSame('app', $all['name']);
        $this->assertSame('php://stdout', $all['stream']);
        $this->assertSame(Level::Debug, $all['level']);
    }

    public function testGetByKey(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $this->assertSame('app', $settings->get('name'));
        $this->assertSame('php://stdout', $settings->get('stream'));
    }

    public function testHasReturnsTrueForExistingKeys(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $this->assertTrue($settings->has('name'));
        $this->assertTrue($settings->has('stream'));
        $this->assertTrue($settings->has('level'));
    }

    public function testHasReturnsFalseForUnknownKey(): void
    {
        $settings = new LoggerSettings('app', 'php://stdout', Level::Debug);
        $this->assertFalse($settings->has('nonexistent'));
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Settings;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Settings\AppSettings;
use Szabmik\Slim\Settings\ArraySettings;
use Szabmik\Slim\Settings\ISettings;

class AppSettingsTest extends TestCase
{
    public function testImplementsSettingsInterface(): void
    {
        $settings = new AppSettings('app', 'production', false, true, true);
        $this->assertInstanceOf(ISettings::class, $settings);
    }

    public function testExtendsArraySettings(): void
    {
        $settings = new AppSettings('app', 'production', false, true, true);
        $this->assertInstanceOf(ArraySettings::class, $settings);
    }

    public function testGetName(): void
    {
        $settings = new AppSettings('my-api', 'dev', false, false, false);
        $this->assertSame('my-api', $settings->getName());
    }

    public function testGetEnv(): void
    {
        $settings = new AppSettings('app', 'staging', false, false, false);
        $this->assertSame('staging', $settings->getEnv());
    }

    public function testGetDisplayErrorDetails(): void
    {
        $settings = new AppSettings('app', 'dev', true, false, false);
        $this->assertTrue($settings->getDisplayErrorDetails());
    }

    public function testGetDisplayErrorDetailsFalse(): void
    {
        $settings = new AppSettings('app', 'production', false, false, false);
        $this->assertFalse($settings->getDisplayErrorDetails());
    }

    public function testGetLogErrors(): void
    {
        $settings = new AppSettings('app', 'dev', false, true, false);
        $this->assertTrue($settings->getLogErrors());
    }

    public function testGetLogErrorDetails(): void
    {
        $settings = new AppSettings('app', 'dev', false, false, true);
        $this->assertTrue($settings->getLogErrorDetails());
    }

    public function testGetReturnsSettingByKey(): void
    {
        $settings = new AppSettings('my-app', 'production', true, true, false);
        $this->assertSame('my-app', $settings->get('name'));
        $this->assertSame('production', $settings->get('env'));
        $this->assertTrue($settings->get('displayErrorDetails'));
    }

    public function testGetReturnsNullForUnknownKey(): void
    {
        $settings = new AppSettings('app', 'dev', false, false, false);
        $this->assertNull($settings->get('nonexistent'));
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $settings = new AppSettings('app', 'dev', false, false, false);
        $this->assertTrue($settings->has('name'));
        $this->assertTrue($settings->has('env'));
    }

    public function testHasReturnsFalseForUnknownKey(): void
    {
        $settings = new AppSettings('app', 'dev', false, false, false);
        $this->assertFalse($settings->has('nonexistent'));
    }

    public function testGetAllReturnsAllSettings(): void
    {
        $settings = new AppSettings('my-app', 'test', true, true, false);
        $all = $settings->getAll();

        $this->assertSame('my-app', $all['name']);
        $this->assertSame('test', $all['env']);
        $this->assertTrue($all['displayErrorDetails']);
        $this->assertTrue($all['logErrors']);
        $this->assertFalse($all['logErrorDetails']);
    }
}

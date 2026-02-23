<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Settings;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Settings\ArraySettings;
use Szabmik\Slim\Settings\JsonSchemaValidatorSettings;
use Szabmik\Slim\Settings\ISettings;

class JsonSchemaValidatorSettingsTest extends TestCase
{
    public function testImplementsSettingsInterface(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas');
        $this->assertInstanceOf(ISettings::class, $settings);
    }

    public function testExtendsArraySettings(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas');
        $this->assertInstanceOf(ArraySettings::class, $settings);
    }

    public function testGetSchemaFolder(): void
    {
        $settings = new JsonSchemaValidatorSettings('/path/to/schemas');
        $this->assertSame('/path/to/schemas', $settings->getSchemaFolder());
    }

    public function testGetPrefixReturnsValue(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas', 'https://example.com/schemas/');
        $this->assertSame('https://example.com/schemas/', $settings->getPrefix());
    }

    public function testGetPrefixDefaultsToNull(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas');
        $this->assertNull($settings->getPrefix());
    }

    public function testGetAllReturnsAllSettings(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas', 'prefix');
        $all = $settings->getAll();

        $this->assertSame('/schemas', $all['schemaFolder']);
        $this->assertSame('prefix', $all['prefix']);
    }

    public function testGetByKey(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas', 'my-prefix');
        $this->assertSame('/schemas', $settings->get('schemaFolder'));
        $this->assertSame('my-prefix', $settings->get('prefix'));
    }

    public function testHasReturnsTrueForExistingKeys(): void
    {
        $settings = new JsonSchemaValidatorSettings('/schemas', 'prefix');
        $this->assertTrue($settings->has('schemaFolder'));
        $this->assertTrue($settings->has('prefix'));
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Setting;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Setting\ArraySettings;
use Szabmik\Slim\Setting\SettingsInterface;

/**
 * Unit tests for ArraySettings abstract class.
 */
class ArraySettingsTest extends TestCase
{
    private ConcreteArraySettings $settings;

    protected function setUp(): void
    {
        $this->settings = new ConcreteArraySettings();
    }

    public function testImplementsSettingsInterface(): void
    {
        $this->assertInstanceOf(SettingsInterface::class, $this->settings);
    }

    public function testGetExistingKey(): void
    {
        $this->settings->setTestSettings([
            'key1' => 'value1',
            'key2' => 123,
        ]);

        $this->assertSame('value1', $this->settings->get('key1'));
        $this->assertSame(123, $this->settings->get('key2'));
    }

    public function testGetNonExistingKey(): void
    {
        $this->settings->setTestSettings(['key1' => 'value1']);

        $this->assertNull($this->settings->get('nonexistent'));
    }

    public function testGetAll(): void
    {
        $settings = [
            'key1' => 'value1',
            'key2' => 123,
            'key3' => ['nested' => 'value'],
        ];
        $this->settings->setTestSettings($settings);

        $this->assertSame($settings, $this->settings->getAll());
    }

    public function testGetAllEmpty(): void
    {
        $this->assertSame([], $this->settings->getAll());
    }

    public function testHasExistingKey(): void
    {
        $this->settings->setTestSettings(['key1' => 'value1']);

        $this->assertTrue($this->settings->has('key1'));
    }

    public function testHasNonExistingKey(): void
    {
        $this->settings->setTestSettings(['key1' => 'value1']);

        $this->assertFalse($this->settings->has('key2'));
    }

    public function testHasWithNullValue(): void
    {
        $this->settings->setTestSettings(['key1' => null]);

        $this->assertFalse($this->settings->has('key1'));
    }

    public function testGetWithDifferentTypes(): void
    {
        $this->settings->setTestSettings([
            'string' => 'text',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'array' => [1, 2, 3],
            'null' => null,
        ]);

        $this->assertSame('text', $this->settings->get('string'));
        $this->assertSame(42, $this->settings->get('int'));
        $this->assertSame(3.14, $this->settings->get('float'));
        $this->assertTrue($this->settings->get('bool'));
        $this->assertSame([1, 2, 3], $this->settings->get('array'));
        $this->assertNull($this->settings->get('null'));
    }

    public function testGetWithZeroValue(): void
    {
        $this->settings->setTestSettings(['key' => 0]);

        $this->assertSame(0, $this->settings->get('key'));
        $this->assertTrue($this->settings->has('key'));
    }

    public function testGetWithEmptyString(): void
    {
        $this->settings->setTestSettings(['key' => '']);

        $this->assertSame('', $this->settings->get('key'));
        $this->assertTrue($this->settings->has('key'));
    }

    public function testGetWithFalseValue(): void
    {
        $this->settings->setTestSettings(['key' => false]);

        $this->assertFalse($this->settings->get('key'));
        $this->assertTrue($this->settings->has('key'));
    }
}

/**
 * Concrete implementation of ArraySettings for testing purposes.
 */
class ConcreteArraySettings extends ArraySettings
{
    /**
     * Helper method to set settings for testing.
     */
    public function setTestSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}

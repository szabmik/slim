<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Warning;

/**
 * Unit tests for Warning class.
 */
class WarningTest extends TestCase
{
    public function testConstructorWithTypeOnly(): void
    {
        $warning = new Warning('DEPRECATION');

        $this->assertSame('DEPRECATION', $warning->getType());
        $this->assertNull($warning->getDescription());
    }

    public function testConstructorWithTypeAndDescription(): void
    {
        $warning = new Warning('DEPRECATION', 'This feature is deprecated');

        $this->assertSame('DEPRECATION', $warning->getType());
        $this->assertSame('This feature is deprecated', $warning->getDescription());
    }

    public function testSetType(): void
    {
        $warning = new Warning('INITIAL_TYPE');
        $result = $warning->setType('NEW_TYPE');

        $this->assertSame('NEW_TYPE', $warning->getType());
        $this->assertSame($warning, $result); // Test fluent interface
    }

    public function testSetDescription(): void
    {
        $warning = new Warning('DEPRECATION');
        $result = $warning->setDescription('New description');

        $this->assertSame('New description', $warning->getDescription());
        $this->assertSame($warning, $result); // Test fluent interface
    }

    public function testSetDescriptionToNull(): void
    {
        $warning = new Warning('DEPRECATION', 'Initial description');
        $warning->setDescription(null);

        $this->assertNull($warning->getDescription());
    }

    public function testJsonSerializeWithDescription(): void
    {
        $warning = new Warning('DEPRECATION', 'This API will be removed in v2');
        $json = $warning->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertSame('DEPRECATION', $json['type']);
        $this->assertSame('This API will be removed in v2', $json['description']);
    }

    public function testJsonSerializeWithoutDescription(): void
    {
        $warning = new Warning('NOTICE');
        $json = $warning->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertSame('NOTICE', $json['type']);
        $this->assertNull($json['description']);
    }

    public function testJsonEncode(): void
    {
        $warning = new Warning('DEPRECATION', 'Use new API instead');
        $jsonString = json_encode($warning);

        $this->assertIsString($jsonString);
        $decoded = json_decode($jsonString, true);
        $this->assertSame('DEPRECATION', $decoded['type']);
        $this->assertSame('Use new API instead', $decoded['description']);
    }

    public function testFluentInterface(): void
    {
        $warning = new Warning('INITIAL');
        $result = $warning
            ->setType('UPDATED_TYPE')
            ->setDescription('Updated description');

        $this->assertSame($warning, $result);
        $this->assertSame('UPDATED_TYPE', $warning->getType());
        $this->assertSame('Updated description', $warning->getDescription());
    }
}

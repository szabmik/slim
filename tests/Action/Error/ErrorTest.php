<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Error;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\Error;

/**
 * Unit tests for Error class.
 */
class ErrorTest extends TestCase
{
    public function testConstructorWithTypeOnly(): void
    {
        $error = new Error('TEST_ERROR');

        $this->assertSame('TEST_ERROR', $error->getType());
        $this->assertNull($error->getDescription());
    }

    public function testConstructorWithTypeAndDescription(): void
    {
        $error = new Error('TEST_ERROR', 'This is a test error');

        $this->assertSame('TEST_ERROR', $error->getType());
        $this->assertSame('This is a test error', $error->getDescription());
    }

    public function testSetType(): void
    {
        $error = new Error('INITIAL_TYPE');
        $result = $error->setType('NEW_TYPE');

        $this->assertSame('NEW_TYPE', $error->getType());
        $this->assertSame($error, $result); // Test fluent interface
    }

    public function testSetDescription(): void
    {
        $error = new Error('TEST_ERROR');
        $result = $error->setDescription('New description');

        $this->assertSame('New description', $error->getDescription());
        $this->assertSame($error, $result); // Test fluent interface
    }

    public function testSetDescriptionToNull(): void
    {
        $error = new Error('TEST_ERROR', 'Initial description');
        $error->setDescription(null);

        $this->assertNull($error->getDescription());
    }

    public function testJsonSerializeWithDescription(): void
    {
        $error = new Error('TEST_ERROR', 'Test description');
        $json = $error->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertSame('TEST_ERROR', $json['type']);
        $this->assertSame('Test description', $json['description']);
    }

    public function testJsonSerializeWithoutDescription(): void
    {
        $error = new Error('TEST_ERROR');
        $json = $error->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertSame('TEST_ERROR', $json['type']);
        $this->assertNull($json['description']);
    }

    public function testJsonEncode(): void
    {
        $error = new Error('TEST_ERROR', 'Test description');
        $jsonString = json_encode($error);

        $this->assertIsString($jsonString);
        $decoded = json_decode($jsonString, true);
        $this->assertSame('TEST_ERROR', $decoded['type']);
        $this->assertSame('Test description', $decoded['description']);
    }

    public function testFluentInterface(): void
    {
        $error = new Error('INITIAL');
        $result = $error
            ->setType('UPDATED_TYPE')
            ->setDescription('Updated description');

        $this->assertSame($error, $result);
        $this->assertSame('UPDATED_TYPE', $error->getType());
        $this->assertSame('Updated description', $error->getDescription());
    }
}

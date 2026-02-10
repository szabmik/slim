<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Error;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\FieldError;

/**
 * Unit tests for FieldError class.
 */
class FieldErrorTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT', 'Invalid email format');

        $this->assertSame('VALIDATION_ERROR', $error->getType());
        $this->assertSame('email', $error->getFieldName());
        $this->assertSame('INVALID_FORMAT', $error->getCode());
        $this->assertSame('Invalid email format', $error->getDescription());
    }

    public function testConstructorWithoutDescription(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');

        $this->assertSame('VALIDATION_ERROR', $error->getType());
        $this->assertSame('email', $error->getFieldName());
        $this->assertSame('INVALID_FORMAT', $error->getCode());
        $this->assertNull($error->getDescription());
    }

    public function testSetFieldName(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');
        $result = $error->setFieldName('username');

        $this->assertSame('username', $error->getFieldName());
        $this->assertSame($error, $result); // Test fluent interface
    }

    public function testSetCode(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');
        $result = $error->setCode('REQUIRED');

        $this->assertSame('REQUIRED', $error->getCode());
        $this->assertSame($error, $result); // Test fluent interface
    }

    public function testSetType(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');
        $result = $error->setType('SCHEMA_ERROR');

        $this->assertSame('SCHEMA_ERROR', $error->getType());
        $this->assertSame($error, $result);
    }

    public function testSetDescription(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');
        $result = $error->setDescription('Email must be valid');

        $this->assertSame('Email must be valid', $error->getDescription());
        $this->assertSame($error, $result);
    }

    public function testJsonSerializeWithAllFields(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT', 'Invalid email format');
        $json = $error->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('fieldName', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertSame('VALIDATION_ERROR', $json['type']);
        $this->assertSame('INVALID_FORMAT', $json['code']);
        $this->assertSame('email', $json['fieldName']);
        $this->assertSame('Invalid email format', $json['description']);
    }

    public function testJsonSerializeWithoutDescription(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'REQUIRED');
        $json = $error->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertSame('VALIDATION_ERROR', $json['type']);
        $this->assertSame('REQUIRED', $json['code']);
        $this->assertSame('email', $json['fieldName']);
        $this->assertNull($json['description']);
    }

    public function testJsonEncode(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'username', 'TOO_SHORT', 'Username is too short');
        $jsonString = json_encode($error);

        $this->assertIsString($jsonString);
        $decoded = json_decode($jsonString, true);
        $this->assertSame('VALIDATION_ERROR', $decoded['type']);
        $this->assertSame('TOO_SHORT', $decoded['code']);
        $this->assertSame('username', $decoded['fieldName']);
        $this->assertSame('Username is too short', $decoded['description']);
    }

    public function testFluentInterface(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');
        $result = $error
            ->setFieldName('newField')
            ->setCode('NEW_CODE')
            ->setType('NEW_TYPE')
            ->setDescription('New description');

        $this->assertSame($error, $result);
        $this->assertSame('newField', $error->getFieldName());
        $this->assertSame('NEW_CODE', $error->getCode());
        $this->assertSame('NEW_TYPE', $error->getType());
        $this->assertSame('New description', $error->getDescription());
    }

    public function testInheritanceFromError(): void
    {
        $error = new FieldError('VALIDATION_ERROR', 'email', 'INVALID_FORMAT');

        $this->assertInstanceOf(\Szabmik\Slim\Action\Error\Error::class, $error);
    }
}

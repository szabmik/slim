<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szabmik\Slim\SchemaValidator\Exception\InvalidJSONSchema;

/**
 * Unit tests for InvalidJSONSchema exception.
 */
class InvalidJSONSchemaTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new InvalidJSONSchema();

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Schema validation failed';
        $exception = new InvalidJSONSchema($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testConstructorWithEmptyMessage(): void
    {
        $exception = new InvalidJSONSchema();

        $this->assertSame('', $exception->getMessage());
    }

    public function testConstructorWithCode(): void
    {
        $exception = new InvalidJSONSchema('Error', 400);

        $this->assertSame(400, $exception->getCode());
    }

    public function testConstructorWithDefaultCode(): void
    {
        $exception = new InvalidJSONSchema('Error');

        $this->assertSame(0, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = new InvalidJSONSchema('Current error', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Invalid schema format';
        $code = 422;
        $previous = new RuntimeException('Validation failed');

        $exception = new InvalidJSONSchema($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(InvalidJSONSchema::class);
        $this->expectExceptionMessage('Test exception');

        throw new InvalidJSONSchema('Test exception');
    }

    public function testCanBeCaught(): void
    {
        try {
            throw new InvalidJSONSchema('Test error', 500);
        } catch (InvalidJSONSchema $e) {
            $this->assertSame('Test error', $e->getMessage());
            $this->assertSame(500, $e->getCode());
        }
    }
}

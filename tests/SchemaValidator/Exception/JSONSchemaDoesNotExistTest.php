<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szabmik\Slim\SchemaValidator\Exception\JSONSchemaDoesNotExist;

/**
 * Unit tests for JSONSchemaDoesNotExist exception.
 */
class JSONSchemaDoesNotExistTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new JSONSchemaDoesNotExist();

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Schema file not found';
        $exception = new JSONSchemaDoesNotExist($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testConstructorWithEmptyMessage(): void
    {
        $exception = new JSONSchemaDoesNotExist();

        $this->assertSame('', $exception->getMessage());
    }

    public function testConstructorWithCode(): void
    {
        $exception = new JSONSchemaDoesNotExist('Error', 404);

        $this->assertSame(404, $exception->getCode());
    }

    public function testConstructorWithDefaultCode(): void
    {
        $exception = new JSONSchemaDoesNotExist('Error');

        $this->assertSame(0, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previous = new RuntimeException('File system error');
        $exception = new JSONSchemaDoesNotExist('Schema not found', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Schema file missing at path /schemas/user.json';
        $code = 404;
        $previous = new RuntimeException('IO error');

        $exception = new JSONSchemaDoesNotExist($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(JSONSchemaDoesNotExist::class);
        $this->expectExceptionMessage('Test exception');

        throw new JSONSchemaDoesNotExist('Test exception');
    }

    public function testCanBeCaught(): void
    {
        try {
            throw new JSONSchemaDoesNotExist('Schema not found', 404);
        } catch (JSONSchemaDoesNotExist $e) {
            $this->assertSame('Schema not found', $e->getMessage());
            $this->assertSame(404, $e->getCode());
        }
    }

    public function testCanBeCaughtAsRuntimeException(): void
    {
        try {
            throw new JSONSchemaDoesNotExist('Schema not found');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(JSONSchemaDoesNotExist::class, $e);
        }
    }
}

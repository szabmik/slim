<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szabmik\Slim\SchemaValidator\Exception\JsonSchemaDoesNotExist;

class JsonSchemaDoesNotExistTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $exception = new JsonSchemaDoesNotExist();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testDefaultConstructor(): void
    {
        $exception = new JsonSchemaDoesNotExist();
        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testWithMessage(): void
    {
        $exception = new JsonSchemaDoesNotExist('JSON schema does not exist. (`user`)');
        $this->assertSame('JSON schema does not exist. (`user`)', $exception->getMessage());
    }

    public function testWithMessageAndCode(): void
    {
        $exception = new JsonSchemaDoesNotExist('Not found', 404);
        $this->assertSame('Not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testWithPreviousException(): void
    {
        $previous = new \RuntimeException('Original');
        $exception = new JsonSchemaDoesNotExist('Wrapped', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}

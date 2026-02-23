<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szabmik\Slim\SchemaValidator\Exception\SchemaReadException;

class SchemaReadExceptionTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $exception = new SchemaReadException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testDefaultConstructor(): void
    {
        $exception = new SchemaReadException();
        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testWithMessage(): void
    {
        $exception = new SchemaReadException('Failed to read content.');
        $this->assertSame('Failed to read content.', $exception->getMessage());
    }

    public function testWithMessageAndCode(): void
    {
        $exception = new SchemaReadException('Read error', 42);
        $this->assertSame('Read error', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
    }

    public function testWithPreviousException(): void
    {
        $previous = new \RuntimeException('Original error');
        $exception = new SchemaReadException('Wrapped', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}

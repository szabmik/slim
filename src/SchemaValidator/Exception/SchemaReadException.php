<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a schema file cannot be read or decoded.
 *
 * This class extends RuntimeException and provides a constructor for creating
 * schema reading errors with customizable error messages, error codes, and
 * exception chaining.
 */
class SchemaReadException extends RuntimeException
{
    /**
     * Constructs a new SchemaReadException exception.
     *
     * @param string         $message  A descriptive error message.
     * @param int            $code     Optional error code.
     * @param Throwable|null $previous Optional previous exception for chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

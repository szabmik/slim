<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a requested JSON Schema file or definition does not exist.
 *
 * This class is typically used to indicate that a schema file could not be found
 * at the expected location, or that a schema reference is missing or undefined.
 *
 * It extends RuntimeException and allows for custom error messages, error codes,
 * and exception chaining.
 */
class JSONSchemaDoesNotExist extends RuntimeException
{
    /**
     * Constructs a new JSONSchemaDoesNotExist exception.
     *
     * @param string         $message  A descriptive error message explaining the missing schema.
     * @param int            $code     Optional error code for the exception.
     * @param Throwable|null $previous Optional previous exception for chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a JSON Schema is found to be invalid.
 *
 * This class extends RuntimeException and is used to signal issues
 * related to malformed or non-compliant JSON Schema definitions.
 *
 * Typical use case: thrown during schema validation when the provided
 * schema does not meet expected standards or contains structural errors.
 */
class InvalidJSONSchema extends RuntimeException
{
    /**
     * Constructs a new InvalidJSONSchema exception.
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

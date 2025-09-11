<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use JsonSerializable;
use Szabmik\Slim\Action\Error\Error;
use Szabmik\Slim\Action\Error\FieldError;

/**
 * Represents a standardized response payload, typically used in API responses.
 *
 * Contains status information, optional data, errors, and warnings.
 * Implements JsonSerializable to support clean JSON conversion for HTTP responses.
 */
class Payload implements JsonSerializable
{
    /**
     * Constructs a new Payload instance.
     *
     * @param int $statusCode HTTP status code representing the outcome of the action (default: 200).
     * @param array|null $data The successful response data, if applicable.
     * @param array|Error|FieldError|null $errors A single or multiple error(s) describing what went wrong.
     * @param array|Warning|null $warnings Optional warnings relevant to the response.
     */
    public function __construct(
        private int $statusCode = 200,
        private object|null|array $data = null,
        private array|null|Error|FieldError $errors = null,
        private array|null|Warning $warnings = null
    ) {
    }

    /**
     * Returns the HTTP status code of the response.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Gets the main payload data, if any.
     *
     * @return object|null|array
     */
    public function getData(): object|null|array
    {
        return $this->data;
    }

    /**
     * Returns one or more errors wrapped in an array.
     *
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->normalizeToArray($this->errors);
    }

    /**
     * Returns one or more warnings wrapped in an array.
     *
     * @return array|null
     */
    public function getWarnings(): ?array
    {
        return $this->normalizeToArray($this->warnings);
    }

    /**
     * Normalizes a value to an array format.
     *
     * If the input is already an array, it returns it unchanged.
     * If it's a single object or scalar, it wraps it in an array.
     * If it's null, it returns null.
     *
     * @param array|object|null $input The value to normalize.
     *
     * @return array|null A normalized array or null.
     */
    private function normalizeToArray(array|object|null $input): ?array
    {
        if (is_null($input)) {
            return null;
        }

        return is_array($input) ? $input : [$input];
    }

    /**
     * Converts the Payload object to a JSON-serializable array.
     *
     * Only includes data, errors, and/or warnings when present.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $payload = [];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->errors !== null) {
            $payload['errors'] = $this->getErrors();
        }

        if (!is_null($this->warnings)) {
            $payload['warnings'] = $this->getWarnings();
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

/**
 * Interface for validating data against a JSON Schema.
 *
 * Implementations of this interface are responsible for checking whether
 * a given data structure conforms to a specified JSON Schema definition.
 *
 * This abstraction allows flexible validation strategies and error reporting.
 */
interface ISchemaValidator
{
    /**
     * Validates the given data against the provided JSON Schema.
     *
     * Both parameters can be either raw JSON strings or decoded PHP objects.
     *
     * @param object|string $data   The data to validate (as object or JSON string).
     * @param object|string $schema The JSON Schema to validate against (as object or JSON string).
     */
    public function validate(object|string $data, object|string $schema): void;

    /**
     * Returns whether the last validation was successful.
     *
     * @return bool True if the data is valid according to the schema; false otherwise.
     */
    public function isValid(): bool;

    /**
     * Returns an array of validation errors from the last validation attempt.
     *
     * Each error may include details such as the path to the invalid property,
     * the expected type or constraint, and a descriptive message.
     *
     * @return array An array of validation error messages or structured error objects.
     */
    public function getErrors(): array;
}

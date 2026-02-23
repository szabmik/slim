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
     *
     * @return SchemaValidationResult The immutable result containing validity and errors.
     */
    public function validate(object|string $data, object|string $schema): SchemaValidationResult;
}

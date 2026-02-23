<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

/**
 * Immutable result object returned by ISchemaValidator::validate().
 *
 * Encapsulates both the validity flag and the structured error list,
 * eliminating the need for stateful isValid()/getErrors() calls on the validator.
 */
class SchemaValidationResult
{
    /**
     * @param bool  $isValid True if the data passed schema validation.
     * @param array<string, mixed> $errors  Structured validation errors, keyed by data path.
     */
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors,
    ) {
    }
}

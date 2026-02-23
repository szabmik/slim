<?php

declare(strict_types=1);

namespace Szabmik\Slim\Attributes;

use Attribute;

/**
 * Attribute that marks a controller or method to perform schema validation on incoming data.
 *
 * This is typically used to validate request bodies or query parameters against a named
 * JSON schema before the main action logic is executed.
 *
 * Example usage:
 * #[ValidateSchema(type: ValidationType::RequestBody, schemaName: "CreateUser")]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ValidateSchema
{
    /**
     * @param ValidationType $type Where to apply schema validation.
     * @param string $schemaName The name of the JSON schema to validate against.
     */
    public function __construct(
        public readonly ValidationType $type,
        public readonly string $schemaName
    ) {
    }
}

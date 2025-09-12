<?php

namespace Szabmik\Slim\Attributes;

use Attribute;

/**
 * Attribute that marks a controller or method to perform schema validation on incoming data.
 *
 * This is typically used to validate request bodies or query parameters against a named
 * JSON schema before the main action logic is executed.
 *
 * Example usage:
 * #[ValidateSchema(type: ValidateSchema::TYPE_REQUEST_BODY, schemaName: "CreateUser")]
 */
#[Attribute]
class ValidateSchema
{
    /** Indicates that the validation target is the HTTP request body. */
    public const TYPE_REQUEST_BODY = 'requestBody';

    /** Indicates that the validation target is the query string parameters. */
    public const TYPE_QUERY_PARAMETERS = 'queryParameters';

    /**
     * @param string $type Where to apply schema validation (e.g. requestBody or queryParameters).
     * @param string $schemaName The name of the JSON schema to validate against.
     */
    public function __construct(
        public string $type,
        public string $schemaName
    ) {
    }
}

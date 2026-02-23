<?php

declare(strict_types=1);

namespace Szabmik\Slim\Enum;

/**
 * Enumeration of standardized error types for application-level actions.
 *
 * These error types are typically used to categorize and communicate
 * the nature of an error in structured responses (e.g. REST API errors).
 */
enum ActionErrorType: string
{
    /** The request is malformed or semantically invalid. */
    case BAD_REQUEST = 'BAD_REQUEST';

    /** The user lacks sufficient privileges to perform the action. */
    case INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';

    /** The action is not allowed in the current context. */
    case NOT_ALLOWED = 'NOT_ALLOWED';

    /** The action is not yet implemented on the server. */
    case NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';

    /** The requested resource was not found. */
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    /** A server-side error occurred that prevented processing. */
    case SERVER_ERROR = 'SERVER_ERROR';

    /** Authentication is required and was either missing or invalid. */
    case UNAUTHENTICATED = 'UNAUTHENTICATED';

    /** The request failed domain-level validation rules. */
    case VALIDATION_ERROR = 'VALIDATION_ERROR';

    /** The request body failed structural (schema-level) validation. */
    case SCHEMA_VALIDATION_ERROR = 'SCHEMA_VALIDATION_ERROR';

    /** The request could not be verified (e.g. invalid token or challenge). */
    case VERIFICATION_ERROR = 'VERIFICATION_ERROR';
}

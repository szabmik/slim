<?php

declare(strict_types=1);

namespace Szabmik\Slim\Attributes;

/**
 * Defines the possible targets for JSON Schema validation on a controller action.
 */
enum ValidationType: string
{
    case RequestBody = 'requestBody';
    case QueryParameters = 'queryParameters';
}

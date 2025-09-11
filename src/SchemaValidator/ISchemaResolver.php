<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

/**
 * Interface for resolving JSON Schema definitions by name.
 *
 * Implementations of this interface are responsible for locating and returning
 * a JSON Schema object based on a given schema name or identifier.
 *
 * This abstraction allows schema resolution to be decoupled from specific storage
 * mechanisms (e.g. file system, database, remote API).
 */
interface ISchemaResolver
{
    /**
     * Resolves and returns a JSON Schema object by its name.
     *
     * @param string $schemaName The name or identifier of the schema to resolve.
     * @return object The resolved JSON Schema as a PHP object.
     */
    public function resolve(string $schemaName): object;
}

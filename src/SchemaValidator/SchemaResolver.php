<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

use JsonException;
use Szabmik\Slim\SchemaValidator\Exception\JsonSchemaDoesNotExist;
use Szabmik\Slim\SchemaValidator\Exception\SchemaReadException;

/**
 * Resolves JSON Schema files from a specified folder.
 *
 * This class implements the ISchemaResolver interface and is responsible for
 * locating, reading, and decoding JSON Schema files stored in the local filesystem.
 *
 * It throws specific exceptions when the schema is missing, unreadable, or invalid.
 */
class SchemaResolver implements ISchemaResolver
{
    /**
     * Constructs a new SchemaResolver.
     *
     * @param string $schemaFolder The directory containing JSON Schema files.
     */
    public function __construct(private string $schemaFolder)
    {
    }

    /**
     * Resolves and returns a JSON Schema object by its name.
     *
     * The method looks for a file named `{schemaName}.json` inside the configured folder,
     * reads its contents, and decodes it into a PHP object.
     *
     * @param string $schemaName The name of the schema file (without `.json` extension).
     * @return object The decoded JSON Schema as a PHP object.
     *
     * @throws JsonSchemaDoesNotExist If the schema file does not exist or cannot be located.
     * @throws SchemaReadException If the file cannot be read.
     * @throws JsonException If the file content is not valid JSON.
     */
    public function resolve(string $schemaName): object
    {
        $schemaRealPath = realpath($this->schemaFolder . '/' . $schemaName . '.json');
        if ($schemaRealPath === false || !file_exists($schemaRealPath)) {
            throw new JsonSchemaDoesNotExist("JSON schema does not exist. (`{$schemaName}`)");
        }

        $canonicalFolder = realpath($this->schemaFolder);
        if ($canonicalFolder === false || !str_starts_with($schemaRealPath, $canonicalFolder . DIRECTORY_SEPARATOR)) {
            throw new JsonSchemaDoesNotExist("Schema path escapes schema folder. (`{$schemaName}`)");
        }

        $schemaContent = file_get_contents($schemaRealPath);
        if ($schemaContent === false) {
            throw new SchemaReadException('Failed to read content.');
        }

        return json_decode($schemaContent, false, 512, JSON_THROW_ON_ERROR);
    }
}

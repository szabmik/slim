<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

use Exception;
use Szabmik\Slim\SchemaValidator\Exception\InvalidJSONSchema;
use Szabmik\Slim\SchemaValidator\Exception\JSONSchemaDoesNotExist;

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
     * @throws JSONSchemaDoesNotExist If the schema file does not exist or cannot be located.
     * @throws Exception If the file cannot be read.
     * @throws InvalidJSONSchema If the file content is not valid JSON.
     */
    public function resolve(string $schemaName): object
    {
        $schemaRealPath = realpath($this->schemaFolder . '/' . $schemaName . '.json');
        if ($schemaRealPath === false || !file_exists($schemaRealPath)) {
            throw new JSONSchemaDoesNotExist("JSON schema does not exist. (`{$schemaName}`)");
        }

        $schemaContent = file_get_contents($schemaRealPath);
        if ($schemaContent === false) {
            throw new Exception('Failed to read content.');
        }

        $decodedSchema = json_decode($schemaContent, false);

        if (!$decodedSchema) {
            throw new InvalidJSONSchema("JSON schema cannot be decoded. (`{$schemaName}`)");
        }

        return $decodedSchema;
    }
}

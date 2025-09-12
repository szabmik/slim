<?php

declare(strict_types=1);

namespace Szabmik\Slim\SchemaValidator;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;

/**
 * Validates data against JSON Schema definitions using the Opis\JsonSchema library.
 *
 * This class implements the ISchemaValidator interface and provides functionality
 * to validate JSON data (as string or object) against a schema, check validity,
 * and retrieve formatted error messages.
 *
 * It supports schema prefix registration for resolving remote or namespaced schemas,
 * and limits the number of reported errors to improve performance and clarity.
 */
class SchemaValidator implements ISchemaValidator
{
    /**
     * Maximum number of validation errors to report.
     */
    protected const MAX_ERRORS = 5;

    /**
     * The underlying Opis validator instance.
     */
    protected Validator $validator;

    /**
     * Stores the result of the last validation attempt.
     */
    protected ValidationResult $lastResult;

    /**
     * Constructs a new SchemaValidator.
     *
     * @param string      $schemaFolder The folder path used for resolving schema files.
     * @param string|null $prefix       Optional prefix for schema resolution (e.g. URI base).
     */
    public function __construct(private string $schemaFolder, private ?string $prefix = null)
    {
        $this->validator = new Validator();

        if (!is_null($prefix)) {
            $resolver = $this->validator->resolver();
            $resolver->registerPrefix($this->prefix, $this->schemaFolder);
        }
        $this->validator->setMaxErrors(self::MAX_ERRORS);
    }

    /**
     * Validates the given data against the provided schema.
     *
     * Both parameters may be raw JSON strings or decoded PHP objects.
     * The result is stored internally and can be queried via `isValid()` and `getErrors()`.
     *
     * @param object|string $data   The data to validate.
     * @param object|string $schema The schema to validate against.
     */
    public function validate(object|string $data, object|string $schema): void
    {
        $this->lastResult = $this->validator->validate($data, $schema);
    }

    /**
     * Indicates whether the last validation was successful.
     *
     * @return bool True if the data is valid; false otherwise.
     */
    public function isValid(): bool
    {
        return $this->lastResult->isValid();
    }

    /**
     * Returns a structured array of validation errors from the last validation.
     *
     * Each error includes:
     * - `keyword`: the failed validation keyword (e.g. "type", "required")
     * - `message`: a human-readable error message
     * - the error path as the array key
     *
     * @return array An associative array of validation errors, keyed by data path.
     */
    public function getErrors(): array
    {
        if ($this->lastResult->hasError()) {
            $formatter = new ErrorFormatter();

            return $formatter->formatKeyed(
                $this->lastResult->error(),
                function (ValidationError $error) use ($formatter) {
                    return [
                        'keyword' => $error->keyword(),
                        'message' => $formatter->formatErrorMessage($error)
                    ];
                },
                fn (ValidationError $error) => implode('.', $error->data()->fullPath())
            );
        }

        return [];
    }
}

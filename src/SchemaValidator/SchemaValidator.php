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
     *
     * @param object|string $data   The data to validate.
     * @param object|string $schema The schema to validate against.
     *
     * @return SchemaValidationResult The immutable result containing validity and errors.
     */
    public function validate(object|string $data, object|string $schema): SchemaValidationResult
    {
        $result = $this->validator->validate($data, $schema);

        if (!$result->hasError()) {
            return new SchemaValidationResult(true, []);
        }

        $formatter = new ErrorFormatter();
        $errors = $formatter->formatKeyed(
            $result->error(),
            function (ValidationError $error) use ($formatter) {
                return [
                    'keyword' => $error->keyword(),
                    'message' => $formatter->formatErrorMessage($error),
                ];
            },
            fn (ValidationError $error) => implode('.', $error->data()->fullPath())
        );

        return new SchemaValidationResult(false, $errors);
    }
}

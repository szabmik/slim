<?php

declare(strict_types=1);

namespace Szabmik\Slim\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use StdClass;
use Szabmik\Slim\Action\Error\Error;
use Szabmik\Slim\Action\Error\FieldError;
use Szabmik\Slim\Action\Payload;
use Szabmik\Slim\Attributes\ValidateSchema;
use Szabmik\Slim\Enum\ActionErrorType;
use Szabmik\Slim\SchemaValidator\ISchemaResolver;
use Szabmik\Slim\SchemaValidator\ISchemaValidator;
use Szabmik\Slim\SchemaValidator\SchemaResolver;
use Szabmik\Slim\SchemaValidator\SchemaValidator;

/**
 * Middleware that validates request bodies or query parameters using JSON Schema definitions.
 *
 * It inspects controller classes for #[ValidateSchema] attributes and applies
 * schema-based validation on the corresponding request payload (body or query).
 * If validation fails, it produces a structured 400 Bad Request response with detailed field errors.
 *
 * Schemas are resolved via ISchemaResolver and validated via ISchemaValidator interfaces,
 * allowing full customization and integration with reusable schema definitions.
 */
class JSONSchemaValidator implements MiddlewareInterface
{
    /**
     * @param string $schemaFolder Path to the folder containing JSON schema files.
     * @param string|null $prefix Optional schema name prefix.
     * @param ISchemaValidator|null $validator Optional custom validator implementation.
     * @param ISchemaResolver|null $schemaResolver Optional custom schema resolver.
     */
    public function __construct(
        protected string $schemaFolder,
        protected ?string $prefix = null,
        protected ?ISchemaValidator $validator = null,
        protected ?ISchemaResolver $schemaResolver = null
    ) {
        $this->validator = $validator ?? new SchemaValidator($schemaFolder, $prefix);
        $this->schemaResolver = $schemaResolver ?? new SchemaResolver($schemaFolder);
    }

    /**
     * Resolves and returns the expected data from the request (body or query) to be validated.
     *
     * @param string $type Source of data (defined by ValidateSchema constants).
     * @param ServerRequestInterface $request The HTTP request.
     *
     * @return object JSON-decoded request data.
     */
    protected function getData(string $type, ServerRequestInterface $request): object
    {
        if ($type === ValidateSchema::TYPE_REQUEST_BODY) {
            $bodyContent = $request->getBody()->getContents();
            if (empty($bodyContent)) {
                return new stdClass();
            }

            return json_decode($bodyContent, false);
        }

        if ($type === ValidateSchema::TYPE_QUERY_PARAMETERS) {
            if (empty($request->getQueryParams())) {
                return new stdClass();
            }

            return json_decode(json_encode($request->getQueryParams()), false);
        }

        return new stdClass();
    }

    /**
     * Retrieves and resolves the schema object based on type and name.
     *
     * @param string $type One of the ValidateSchema types.
     * @param string $schemaName Name of the schema to load.
     *
     * @return object JSON schema object.
     */

    protected function getSchema(string $type, string $schemaName): object
    {
        $prefix = '';
        if ($type === ValidateSchema::TYPE_REQUEST_BODY) {
            $prefix = 'RequestBody/';
        }

        if ($type === ValidateSchema::TYPE_QUERY_PARAMETERS) {
            $prefix = 'QueryParameters/';
        }

        return $this->schemaResolver->resolve($prefix . $schemaName);
    }

    /**
     * Executes the validation logic by extracting the #[ValidateSchema] attributes from the action class.
     *
     * If any schema validation fails, a 400 error response is returned immediately.
     *
     * @param ServerRequestInterface $request Incoming HTTP request.
     * @param RequestHandlerInterface $handler Next middleware or controller.
     *
     * @return ResponseInterface HTTP response (either validation error or result of next handler).
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $action = $routeContext->getRoute()->getCallable();

        $reflection = new ReflectionClass($action);
        $attributes = $reflection->getAttributes(ValidateSchema::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $type = $instance->type;
            $schemaName = $instance->schemaName;
            $data = $this->getData($type, $request);
            $schema = $this->getSchema($type, $schemaName);

            $this->validator->validate($data, $schema);
            if (!$this->validator->isValid()) {
                return $this->errorHandler();
            }
        }

        $response = $handler->handle($request);
        return $response;
    }

    /**
     * Returns the validator instance used for schema validation.
     *
     * This allows access to the underlying ISchemaValidator, which may be mocked,
     * extended or inspected during testing or custom behavior injection.
     *
     * @return ISchemaValidator
     */
    protected function getValidator(): ISchemaValidator
    {
        return $this->validator;
    }

    /**
     * Returns the schema resolver responsible for loading JSON schema files.
     *
     * Used internally to locate and resolve schema definitions based on type and name.
     *
     * @return ISchemaResolver
     */
    protected function getSchemaResolver(): ISchemaResolver
    {
        return $this->schemaResolver;
    }

    /**
     * Extracts the content enclosed in the first pair of parentheses.
     *
     * Example: "The property (email) is required" → "email"
     * If no match is found, null is returned.
     *
     * @param string $content Error message string.
     *
     * @return string|null Extracted content or null if not found.
     */
    protected function extractContent(string $content): ?string
    {
        preg_match('/\((.*?)\)/', $content, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Generates a standard validation error code from a variable and keyword.
     *
     * Example: "email" + "required" → "EMAIL_REQUIRED"
     * CamelCase variable names will be converted to snake case before uppercasing.
     *
     * @param string $variableName The affected field name (e.g. "userName").
     * @param string $keyword The schema keyword triggering the error (e.g. "minLength").
     *
     * @return string A normalized validation code (e.g. "USER_NAME_MIN_LENGTH").
     */
    protected function generateCode(string $variableName, string $keyword): string
    {
        $modifiedName = preg_replace('/(?<!^)(?=[A-Z])/', '_', $variableName);
        return strtoupper("{$modifiedName}_{$keyword}");
    }

    /**
     * Creates a detailed error response with a structured payload of validation issues.
     *
     * Maps validator errors into API-friendly field errors, including property names and codes.
     *
     * @throws Exception If JSON encoding fails.
     *
     * @return ResponseInterface A JSON error response with HTTP 400 status.
     */
    public function errorHandler(): ResponseInterface
    {
        $errors = [];

        foreach ($this->validator->getErrors() as $key => $errorArray) {
            foreach ($errorArray as $item) {
                $fieldNames = [];
                if ('' === (string)$key) {
                    if ('required' === $item['keyword']) {
                        $match = $this->extractContent($item['message']);
                        $fieldNames = !is_null($match) ? explode(', ', $match) : [];
                    }
                    if (empty($fieldNames)) {
                        $errors[] = new Error(ActionErrorType::VALIDATION_ERROR->value, $item['message']);
                    } else {
                        foreach ($fieldNames as $fieldName) {
                            $errors[] = new FieldError(
                                ActionErrorType::VALIDATION_ERROR->value,
                                $fieldName,
                                $this->generateCode($fieldName, $item['keyword']),
                                "The required property (`{$fieldName}`) is missing."
                            );
                        }
                    }
                } else {
                    $errors[] = new FieldError(
                        ActionErrorType::VALIDATION_ERROR->value,
                        (string)$key,
                        $this->generateCode((string)$key, $item['keyword']),
                        $item['message']
                    );
                }
            }
        }

        $payload = new Payload(400, null, $errors);

        $response = new Response();
        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);
        if ($encodedPayload === false) {
            throw new Exception('Failed to encode json.');
        }
        $response->getBody()->write($encodedPayload);

        return $response
            ->withStatus($payload->getStatusCode())
            ->withHeader('Content-Type', 'application/json');
    }
}

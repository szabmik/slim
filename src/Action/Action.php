<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * Base abstract controller for Slim actions.
 *
 * This class provides common functionality for handling HTTP requests, extracting route arguments,
 * and formatting JSON responses. Subclasses must implement the `action()` method.
 */
abstract class Action
{
    /** @var ServerRequestInterface The current PSR-7 request object */
    protected ServerRequestInterface $request;

    /** @var ResponseInterface The current PSR-7 response object */
    protected ResponseInterface $response;

    /** @var array The current route arguments */
    protected array $args;

    /**
     * Subclasses must implement this method to define the actual action logic.
     *
     * @return ResponseInterface
     */
    abstract protected function action(): ResponseInterface;

    /**
     * Constructs the Action instance with optional PSR-3 logger support.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(protected ?LoggerInterface $logger)
    {
    }

    /**
     * Magic invoke method for Slim-compatible route invocation.
     * Stores the request, response and route arguments before executing the action logic.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    /**
     * Retrieves a required route argument or throws a 400 Bad Request if missing.
     *
     * @param string $name
     *
     * @throws HttpBadRequestException
     *
     * @return string
     */
    protected function resolveArg(string $name): string
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * Checks whether a route argument exists.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function hasArg(string $name): bool
    {
        return isset($this->args[$name]);
    }

    /**
     * Creates a JSON response from data with a given status code.
     *
     * @param object|array|null $data
     * @param int $statusCode
     *
     * @return ResponseInterface
     */
    protected function respondWithData(null|object|array $data = null, int $statusCode = 200): ResponseInterface
    {
        $payload = new Payload($statusCode, $data);

        return $this->respond($payload);
    }

    /**
     * Creates an empty response payload with the given HTTP status code.
     *
     * Commonly used for responses that do not return content (e.g. 204 No Content),
     * but still require a standard JSON structure for consistency.
     *
     * @param int $statusCode The HTTP status code to return (default: 204).
     *
     * @return ResponseInterface
     */
    protected function respondWithoutData(int $statusCode = 204): ResponseInterface
    {
        $payload = new Payload($statusCode);

        return $this->respond($payload);
    }

    /**
     * Writes the serialized JSON Payload into the response body.
     *
     * @param Payload $payload
     *
     * @throws Exception If JSON encoding fails.
     *
     * @return ResponseInterface
     */
    protected function respond(Payload $payload): ResponseInterface
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new Exception('Failed to encode json.');
        }
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }
}

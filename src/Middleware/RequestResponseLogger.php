<?php

declare(strict_types=1);

namespace Szabmik\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use JsonException;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteContext;

/**
 * Middleware that logs incoming HTTP requests and outgoing responses.
 *
 * This logger captures the URI, HTTP method, headers, and parsed body of the request,
 * as well as the response status, headers, body (if JSON), and total execution time in milliseconds.
 * The logs are typically used for debugging, tracing, and performance analysis,
 * and are compatible with structured log aggregators such as Graylog.
 */
class RequestResponseLogger implements MiddlewareInterface
{
    /**
     * @param LoggerInterface $logger PSR-3-compliant logger to capture request and response data.
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Processes the request, logs structured details about the request and response,
     * and measures execution time of the downstream handler.
     *
     * @param ServerRequestInterface $request HTTP request received from the client.
     * @param RequestHandlerInterface $handler The next request handler in the middleware stack.
     *
     * @return ResponseInterface The final HTTP response to be returned to the client.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);

        $requestHeaders = $this->getHeadersAsString($request->getHeaders());

        $routeContext = RouteContext::fromRequest($request);
        $pattern = $routeContext->getRoute()?->getPattern() ?? '';

        $this->logger->info(
            sprintf(
                'Incoming %s request to %s',
                $request->getMethod(),
                $request->getUri()->getPath()
            ),
            [
                'uri' => (string)$request->getUri(),
                'route' => $pattern,
                'method' => $request->getMethod(),
            ]
        );

        $this->logger->debug(
            sprintf(
                'Incoming %s request to %s details',
                $request->getMethod(),
                $request->getUri()->getPath()
            ),
            [
                'uri' => (string)$request->getUri(),
                'route' => $pattern,
                'method' => $request->getMethod(),
                'header' => $requestHeaders,
                'body' => json_encode($request->getParsedBody(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]
        );
        
        $response = $handler->handle($request);

        $responseHeaders = $this->getHeadersAsString($response->getHeaders());

        $this->logger->info(
            sprintf(
                'Responded with %d to %s %s',
                $response->getStatusCode(),
                $request->getMethod(),
                $request->getUri()->getPath(),
            ),
            [
                'responseTimeMs' => round((microtime(true) - $start) * 1000, 2),
                'uri' => $request->getUri()->__toString(),
                'route' => $pattern,
                'statusCode' => $response->getStatusCode()
            ]
        );

        $this->logger->debug(
            sprintf(
                'Responded with %d to %s %s details',
                $response->getStatusCode(),
                $request->getMethod(),
                $request->getUri()->getPath(),
            ),
            [
                'responseTimeMs' => round((microtime(true) - $start) * 1000, 2),
                'uri' => $request->getUri()->__toString(),
                'route' => $pattern,
                'statusCode' => $response->getStatusCode(),
                'header' => $responseHeaders,
                'body' => $this->getResponseBodyForLog($response)
            ]
        );

        return $response;
    }

    /**
     * Reads and returns the response body for logging purposes.
     *
     * Rewinds the stream before reading to ensure the full body is captured
     * regardless of prior reads, then rewinds again so downstream consumers
     * receive the stream at position 0.
     *
     * @param ResponseInterface $response The HTTP response.
     *
     * @return string|null The JSON-re-encoded body, or null if not valid JSON.
     */
    private function getResponseBodyForLog(ResponseInterface $response): ?string
    {
        $body = $response->getBody();
        $body->rewind();
        $content = $body->getContents();
        $body->rewind();

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Converts an associative array of headers to a readable string format for logging.
     *
     * @param array<string, string[]> $headers The headers to stringify.
     *
     * @return string Semicolon-separated string of header names and values.
     */
    private function getHeadersAsString(array $headers): string
    {
        return implode(
            '; ',
            array_map(
                fn ($name, $values) => "$name: " . implode(', ', $values),
                array_keys($headers),
                $headers
            )
        );
    }
}

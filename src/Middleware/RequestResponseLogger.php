<?php

declare(strict_types=1);

namespace Szabmik\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

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

        $this->logger->debug(
            'Request has been received',
            [
                'uri' => $request->getUri()->__toString(),
                'method' => $request->getMethod(),
                'headers' => $requestHeaders,
                'body' => $request->getParsedBody()
            ]
        );
        $response = $handler->handle($request);

        $responseHeaders = $this->getHeadersAsString($response->getHeaders());

        $this->logger->debug(
            'Response has been sent',
            [
                'responseTimeMs' => round((microtime(true) - $start) * 1000, 2),
                'uri' => $request->getUri()->__toString(),
                'statusCode' => $response->getStatusCode(),
                'headers' => $responseHeaders,
                'body' => json_decode((string)$response->getBody(), true)
            ]
        );

        return $response;
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

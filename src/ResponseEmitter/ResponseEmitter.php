<?php

declare(strict_types=1);

namespace Szabmik\Slim\ResponseEmitter;

use Psr\Http\Message\ResponseInterface;
use Slim\ResponseEmitter as SlimResponseEmitter;

/**
 * Custom ResponseEmitter that extends Slim's emitter with enhanced CORS and caching headers.
 *
 * This class overrides the default `emit()` behavior of Slim's ResponseEmitter to inject
 * HTTP headers that support Cross-Origin Resource Sharing (CORS) and prevent caching.
 *
 * Key features:
 * - Dynamically sets `Access-Control-Allow-Origin` based on the request's origin header.
 * - Allows credentials and custom headers for secure API communication across domains.
 * - Prevents caching by setting strict `Cache-Control` and `Pragma` headers.
 * - Ensures output buffering doesn't interfere with response emission.
 *
 * Use this emitter if your Slim-based API is intended to be consumed from multiple
 * front-end origins (e.g. SPAs or mobile apps), especially during development or if
 * you require fine-grained control over CORS behavior.
 */
class ResponseEmitter extends SlimResponseEmitter
{
    /**
     * Emits the HTTP response with additional CORS and cache-control headers.
     *
     * This method overrides the parent emitter to dynamically set
     * CORS (Cross-Origin Resource Sharing) headers based on the request origin,
     * allowing client applications from other origins to access the API.
     * It also disables client- and proxy-side caching to ensure fresh responses.
     *
     * @param ResponseInterface $response The PSR-7 response to emit.
     */
    public function emit(ResponseInterface $response): void
    {
        // This variable should be set to the allowed host from which your API can be accessed with
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        $response = $response
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization',
            )
            ->withHeader('Access-Control-Allow-Methods', 'POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');

        if (ob_get_contents()) {
            ob_clean();
        }

        parent::emit($response);
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Middleware that normalizes request URIs by removing trailing slashes.
 *
 * Ensures that incoming requests without query parameters are redirected to their
 * non-trailing-slash equivalent (e.g. `/example/` → `/example`) using a 301 redirect.
 * If the path is already normalized, it allows the request to pass through unmodified.
 *
 * Helps avoid duplicate routing definitions and improves SEO consistency.
 */
class RemoveTrailingSlash implements MiddlewareInterface
{
    /**
     * Processes the incoming request and removes trailing slashes from the path.
     *
     * If the path differs after normalization, returns a 301 redirect to the canonical form.
     * Otherwise, continues the request lifecycle with the normalized URI.
     *
     * @param ServerRequestInterface $request HTTP request object.
     * @param RequestHandlerInterface $handler Next handler in the middleware stack.
     *
     * @return ResponseInterface Either a redirect response or the result of the next handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $this->normalize($uri->getPath());

        if ($uri->getPath() !== $path) {
            return (new Response())
                ->withHeader('Location', (string) $uri->withPath($path))
                ->withStatus(301);
        }

        return $handler->handle($request->withUri($uri->withPath($path)));
    }

    /**
     * Normalizes a URI path by removing trailing slashes except for the root.
     *
     * @param string $path The URI path to normalize.
     *
     * @return string The normalized path.
     */
    private function normalize(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        if (strlen($path) > 1) {
            return rtrim($path, '/');
        }

        return $path;
    }
}

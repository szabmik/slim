<?php

declare(strict_types=1);

namespace Szabmik\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RemoveTrailingSlash implements MiddlewareInterface
{
    private $responseFactory;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $this->normalize($uri->getPath());

        if ($this->responseFactory && ($uri->getPath() !== $path)) {
            return $this->responseFactory->createResponse(301)
                ->withHeader('Location', (string) $uri->withPath($path));
        }

        return $handler->handle($request->withUri($uri->withPath($path)));
    }

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

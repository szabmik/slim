<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Szabmik\Slim\Middleware\RemoveTrailingSlash;

/**
 * Unit tests for RemoveTrailingSlash middleware.
 */
class RemoveTrailingSlashTest extends TestCase
{
    private RemoveTrailingSlash $middleware;

    protected function setUp(): void
    {
        $this->middleware = new RemoveTrailingSlash();
    }

    public function testImplementsMiddlewareInterface(): void
    {
        $this->assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testProcessWithTrailingSlashReturnsRedirect(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/example/');
        $uri->method('withPath')->with('/example')->willReturnSelf();
        $uri->method('__toString')->willReturn('http://example.com/example');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
    }

    public function testProcessWithoutTrailingSlashContinuesChain(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/example');
        $uri->method('withPath')->with('/example')->willReturnSelf();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('withUri')->willReturnSelf();

        $expectedResponse = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
    }

    public function testProcessWithRootPathReturnsSlash(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/');
        $uri->method('withPath')->with('/')->willReturnSelf();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('withUri')->willReturnSelf();

        $expectedResponse = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
    }

    public function testProcessWithEmptyPathNormalizesToRoot(): void
    {
        // When path is empty, it normalizes to "/" which is different, triggering a redirect
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('');
        $uri->method('withPath')->with('/')->willReturnSelf();
        $uri->method('__toString')->willReturn('http://example.com/');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = $this->middleware->process($request, $handler);

        // Should return a 301 redirect since '' != '/'
        $this->assertSame(301, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
    }

    public function testProcessWithMultipleTrailingSlashes(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/example///');
        $uri->method('withPath')->with('/example')->willReturnSelf();
        $uri->method('__toString')->willReturn('http://example.com/example');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame(301, $response->getStatusCode());
    }

    public function testProcessWithNestedPath(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/api/v1/users/');
        $uri->method('withPath')->with('/api/v1/users')->willReturnSelf();
        $uri->method('__toString')->willReturn('http://example.com/api/v1/users');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame(301, $response->getStatusCode());
    }
}

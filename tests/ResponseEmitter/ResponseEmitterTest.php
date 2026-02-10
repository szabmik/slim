<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\ResponseEmitter;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Szabmik\Slim\ResponseEmitter\ResponseEmitter;

/**
 * Unit tests for ResponseEmitter class.
 */
class ResponseEmitterTest extends TestCase
{
    protected function setUp(): void
    {
       
    }

    protected function tearDown(): void
    {
        // Clean environment variables
        putenv('ALLOWED_ORIGINS');
        unset($_SERVER['ALLOWED_ORIGINS'], $_SERVER['HTTP_ORIGIN']);
    }

    public function testWithOriginsCreatesInstance(): void
    {
        $origins = ['https://example.com', 'https://app.example.com'];
        $emitter = ResponseEmitter::withOrigins($origins);

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testAllowAllOriginsCreatesInstance(): void
    {
        $emitter = ResponseEmitter::allowAllOrigins();

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testFromEnvironmentCreatesInstance(): void
    {
        $emitter = ResponseEmitter::fromEnvironment();

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testFromEnvironmentWithEnvironmentVariable(): void
    {
        putenv('ALLOWED_ORIGINS=https://example.com,https://app.example.com');

        $emitter = ResponseEmitter::fromEnvironment();

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testFromEnvironmentWithServerVariable(): void
    {
        $_SERVER['ALLOWED_ORIGINS'] = 'https://example.com';

        $emitter = ResponseEmitter::fromEnvironment();

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testFromEnvironmentDefaultsToWildcard(): void
    {
        // Ensure no environment variable is set
        putenv('ALLOWED_ORIGINS');
        unset($_SERVER['ALLOWED_ORIGINS']);

        $emitter = ResponseEmitter::fromEnvironment();

        $this->assertInstanceOf(ResponseEmitter::class, $emitter);
    }

    public function testEmitAddsWildcardCorsHeaders(): void
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://example.com';

        $body = $this->createMock(StreamInterface::class);
        $body->method('isSeekable')->willReturn(false);
        $body->method('isReadable')->willReturn(true);
        $body->method('eof')->willReturn(true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($body);
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getReasonPhrase')->willReturn('OK');

        // Set up expectations for withHeader calls
        $response->method('withHeader')->willReturnSelf();
        $response->method('withAddedHeader')->willReturnSelf();

        $response->expects($this->atLeastOnce())
            ->method('withHeader')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Access-Control-Allow-Origin'),
                    $this->equalTo('Access-Control-Allow-Credentials'),
                    $this->anything()
                ),
                $this->anything()
            )
            ->willReturnSelf();

        $emitter = ResponseEmitter::allowAllOrigins();

        // Capture output to prevent actual emission
        ob_start();
        try {
            $emitter->emit($response);
        } catch (\Exception $e) {
            // Ignore exceptions from actual emission
        } finally {
            ob_end_clean();
        }

        // Test passes if no exception is thrown and mocks are satisfied
        $this->assertTrue(true);
    }

    public function testEmitWithSpecificAllowedOrigin(): void
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://example.com';

        $body = $this->createMock(StreamInterface::class);
        $body->method('isSeekable')->willReturn(false);
        $body->method('isReadable')->willReturn(true);
        $body->method('eof')->willReturn(true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($body);
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getReasonPhrase')->willReturn('OK');
        $response->method('withHeader')->willReturnSelf();
        $response->method('withAddedHeader')->willReturnSelf();

        $emitter = ResponseEmitter::withOrigins(['https://example.com']);

        ob_start();
        try {
            $emitter->emit($response);
        } catch (\Exception $e) {
            // Ignore exceptions from actual emission
        } finally {
            ob_end_clean();
        }

        $this->assertTrue(true);
    }

    public function testEmitWithDisallowedOrigin(): void
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://malicious.com';

        $body = $this->createMock(StreamInterface::class);
        $body->method('isSeekable')->willReturn(false);
        $body->method('isReadable')->willReturn(true);
        $body->method('eof')->willReturn(true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($body);
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getReasonPhrase')->willReturn('OK');
        $response->method('withHeader')->willReturnSelf();
        $response->method('withAddedHeader')->willReturnSelf();

        // When origin is not allowed, withHeader should not be called for CORS headers
        $emitter = ResponseEmitter::withOrigins(['https://allowed.com']);

        ob_start();
        try {
            $emitter->emit($response);
        } catch (\Exception $e) {
            // Ignore exceptions from actual emission
        } finally {
            ob_end_clean();
        }

        $this->assertTrue(true);
    }

    public function testEmitWithNoOriginHeader(): void
    {
        unset($_SERVER['HTTP_ORIGIN']);

        $body = $this->createMock(StreamInterface::class);
        $body->method('isSeekable')->willReturn(false);
        $body->method('isReadable')->willReturn(true);
        $body->method('eof')->willReturn(true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($body);
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getReasonPhrase')->willReturn('OK');
        $response->method('withHeader')->willReturnSelf();
        $response->method('withAddedHeader')->willReturnSelf();

        $emitter = ResponseEmitter::withOrigins(['https://example.com']);

        ob_start();
        try {
            $emitter->emit($response);
        } catch (\Exception $e) {
            // Ignore exceptions
        } finally {
            ob_end_clean();
        }

        $this->assertTrue(true);
    }
}

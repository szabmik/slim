<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\ServiceStatus;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Szabmik\Slim\Action\ServiceStatus\Liveness;

/**
 * Unit tests for Liveness action.
 */
class LivenessTest extends TestCase
{
    public function testLivenessReturns204(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/liveness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Liveness();

        $result = $action($request, $response, []);

        $this->assertSame(204, $result->getStatusCode());
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
    }

    public function testLivenessReturnsJsonPayload(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/liveness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Liveness();

        $result = $action($request, $response, []);

        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertIsArray($decoded);
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\ServiceStatus;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Szabmik\Slim\Action\ServiceStatus\Health;
use Szabmik\Slim\Enum\ServiceStatus;

/**
 * Unit tests for Health action.
 */
class HealthTest extends TestCase
{
    public function testHealthReturns200WithStatusHealthy(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/health');
        $response = (new ResponseFactory())->createResponse();
        $action = new Health();

        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertSame('no-store', $result->getHeaderLine('Cache-Control'));

        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('status', $decoded['data']);
        $this->assertSame(ServiceStatus::Healthy->value, $decoded['data']['status']);
        $this->assertArrayHasKey('timestamp', $decoded['data']);
    }

    public function testHealthReturnsValidIso8601Timestamp(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/health');
        $response = (new ResponseFactory())->createResponse();
        $action = new Health();

        $result = $action($request, $response, []);

        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);
        $timestamp = $decoded['data']['timestamp'];

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[\+\-]\d{2}:\d{2}$/',
            $timestamp,
            'Timestamp should be ISO 8601 format'
        );
    }
}

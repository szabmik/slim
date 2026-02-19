<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\ServiceStatus;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Szabmik\Slim\Action\ServiceStatus\IReadinessCheck;
use Szabmik\Slim\Action\ServiceStatus\Readiness;
use Szabmik\Slim\Action\ServiceStatus\ReadinessCheckRegistry;
use Szabmik\Slim\Enum\ServiceStatus;

/**
 * Unit tests for Readiness action.
 */
class ReadinessTest extends TestCase
{
    public function testReadinessReturns200WhenAllRequiredChecksHealthy(): void
    {
        $check = $this->createHealthyCheck('db');
        $registry = new ReadinessCheckRegistry();
        $registry->register($check);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/readiness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Readiness($registry);

        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertSame('no-store', $result->getHeaderLine('Cache-Control'));

        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame(ServiceStatus::Healthy->value, $decoded['data']['status']);
        $this->assertArrayHasKey('checked_at', $decoded['data']);
        $this->assertArrayHasKey('components', $decoded['data']);
        $this->assertArrayHasKey('db', $decoded['data']['components']);
        $this->assertSame(ServiceStatus::Healthy->value, $decoded['data']['components']['db']['status']);
    }

    public function testReadinessReturns503WhenRequiredCheckUnhealthy(): void
    {
        $check = $this->createUnhealthyCheck('db');
        $registry = new ReadinessCheckRegistry();
        $registry->register($check);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/readiness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Readiness($registry);

        $result = $action($request, $response, []);

        $this->assertSame(503, $result->getStatusCode());

        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertSame(ServiceStatus::Unhealthy->value, $decoded['data']['status']);
        $this->assertSame(ServiceStatus::Unhealthy->value, $decoded['data']['components']['db']['status']);
    }

    public function testReadinessReturns200WhenOptionalCheckUnhealthy(): void
    {
        $required = $this->createHealthyCheck('db');
        $optional = $this->createOptionalUnhealthyCheck('cache');

        $registry = new ReadinessCheckRegistry();
        $registry->register($required);
        $registry->register($optional);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/readiness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Readiness($registry);

        $result = $action($request, $response, []);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testReadinessIncludesComponentDetails(): void
    {
        $check = $this->createMock(IReadinessCheck::class);
        $check->method('isReady')->willReturn(true);
        $check->method('getName')->willReturn('custom');
        $check->method('getDetails')->willReturn(['message' => 'All good']);
        $check->method('isRequired')->willReturn(true);

        $registry = new ReadinessCheckRegistry();
        $registry->register($check);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/readiness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Readiness($registry);

        $result = $action($request, $response, []);
        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertSame(['message' => 'All good'], $decoded['data']['components']['custom']['details']);
        $this->assertTrue($decoded['data']['components']['custom']['required']);
    }

    public function testReadinessReturnsValidIso8601CheckedAt(): void
    {
        $registry = new ReadinessCheckRegistry();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/readiness');
        $response = (new ResponseFactory())->createResponse();
        $action = new Readiness($registry);

        $result = $action($request, $response, []);
        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[\+\-]\d{2}:\d{2}$/',
            $decoded['data']['checked_at'],
            'checked_at should be ISO 8601 format'
        );
    }

    private function createHealthyCheck(string $name): IReadinessCheck
    {
        $check = $this->createMock(IReadinessCheck::class);
        $check->method('isReady')->willReturn(true);
        $check->method('getName')->willReturn($name);
        $check->method('getDetails')->willReturn([]);
        $check->method('isRequired')->willReturn(true);

        return $check;
    }

    private function createUnhealthyCheck(string $name): IReadinessCheck
    {
        $check = $this->createMock(IReadinessCheck::class);
        $check->method('isReady')->willReturn(false);
        $check->method('getName')->willReturn($name);
        $check->method('getDetails')->willReturn(['error' => 'Connection failed']);
        $check->method('isRequired')->willReturn(true);

        return $check;
    }

    private function createOptionalUnhealthyCheck(string $name): IReadinessCheck
    {
        $check = $this->createMock(IReadinessCheck::class);
        $check->method('isReady')->willReturn(false);
        $check->method('getName')->willReturn($name);
        $check->method('getDetails')->willReturn([]);
        $check->method('isRequired')->willReturn(false);

        return $check;
    }
}

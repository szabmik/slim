<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Szabmik\Slim\Action\Action;
use Szabmik\Slim\Action\Payload;

/**
 * Unit tests for Action base class.
 */
class ActionTest extends TestCase
{
    private ServerRequestInterface $request;

    private ResponseInterface $response;

    protected function setUp(): void
    {
        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $this->request = $requestFactory->createServerRequest('GET', '/test');
        $this->response = $responseFactory->createResponse();
    }

    public function testInvokeStoresRequestResponseAndArgs(): void
    {
        $args = ['id' => '123'];
        $action = new TestableAction();

        $response = $action($this->request, $this->response, $args);

        $this->assertSame($this->request, $action->getStoredRequest());
        $this->assertSame($this->response, $action->getStoredResponse());
        $this->assertSame($args, $action->getStoredArgs());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testResolveArgReturnsValueWhenPresent(): void
    {
        $args = ['id' => '42'];
        $action = new TestableAction();
        $action($this->request, $this->response, $args);

        $value = $action->publicResolveArg('id');

        $this->assertSame('42', $value);
    }

    public function testResolveArgThrowsWhenMissing(): void
    {
        $action = new TestableAction();
        $action($this->request, $this->response, []);

        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Could not resolve argument `missing`.');

        $action->publicResolveArg('missing');
    }

    public function testHasArgReturnsTrueWhenPresent(): void
    {
        $args = ['id' => '1'];
        $action = new TestableAction();
        $action($this->request, $this->response, $args);

        $this->assertTrue($action->publicHasArg('id'));
    }

    public function testHasArgReturnsFalseWhenMissing(): void
    {
        $action = new TestableAction();
        $action($this->request, $this->response, []);

        $this->assertFalse($action->publicHasArg('id'));
    }

    public function testRespondWithDataReturnsJsonWithStatusCode(): void
    {
        $action = new TestableAction();
        $action($this->request, $this->response, []);

        $response = $action->publicRespondWithData(['key' => 'value'], 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame(['key' => 'value'], $decoded['data']);
    }

    public function testRespondWithoutDataReturnsEmptyPayloadWithStatusCode(): void
    {
        $action = new TestableAction();
        $action($this->request, $this->response, []);

        $response = $action->publicRespondWithoutData(204);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
    }

    public function testActionConstructorAcceptsNullLogger(): void
    {
        $action = new TestableAction();

        $this->assertInstanceOf(Action::class, $action);
    }

    public function testActionConstructorAcceptsLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $action = new TestableAction($logger);

        $this->assertInstanceOf(Action::class, $action);
    }
}

/**
 * Concrete implementation of Action for testing.
 */
final class TestableAction extends Action
{
    public function getStoredRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getStoredResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStoredArgs(): array
    {
        return $this->args;
    }

    public function publicResolveArg(string $name): string
    {
        return $this->resolveArg($name);
    }

    public function publicHasArg(string $name): bool
    {
        return $this->hasArg($name);
    }

    public function publicRespondWithData(null|object|array $data = null, int $statusCode = 200): ResponseInterface
    {
        return $this->respondWithData($data, $statusCode);
    }

    public function publicRespondWithoutData(int $statusCode = 204): ResponseInterface
    {
        return $this->respondWithoutData($statusCode);
    }

    protected function action(): ResponseInterface
    {
        return $this->response;
    }
}

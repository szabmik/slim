<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\Error;
use Szabmik\Slim\Action\Error\FieldError;
use Szabmik\Slim\Action\Payload;
use Szabmik\Slim\Action\Warning;

class PayloadTest extends TestCase
{
    public function testDefaultStatusCodeIs200(): void
    {
        $payload = new Payload();
        $this->assertSame(200, $payload->getStatusCode());
    }

    public function testCustomStatusCode(): void
    {
        $payload = new Payload(422);
        $this->assertSame(422, $payload->getStatusCode());
    }

    public function testDataDefaultsToNull(): void
    {
        $payload = new Payload();
        $this->assertNull($payload->getData());
    }

    public function testGetDataReturnsArray(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $payload = new Payload(200, $data);
        $this->assertSame($data, $payload->getData());
    }

    public function testGetDataReturnsObject(): void
    {
        $data = (object) ['id' => 1];
        $payload = new Payload(200, $data);
        $this->assertSame($data, $payload->getData());
    }

    public function testGetErrorsReturnsNullWhenNotSet(): void
    {
        $payload = new Payload();
        $this->assertNull($payload->getErrors());
    }

    public function testGetErrorsWrapsSingleErrorInArray(): void
    {
        $error = new Error('not_found', 'Resource missing.');
        $payload = new Payload(404, null, $error);

        $result = $payload->getErrors();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($error, $result[0]);
    }

    public function testGetErrorsPassesThroughArray(): void
    {
        $errors = [new Error('error_a'), new Error('error_b')];
        $payload = new Payload(400, null, $errors);

        $result = $payload->getErrors();
        $this->assertSame($errors, $result);
    }

    public function testGetErrorsWithFieldError(): void
    {
        $fieldError = new FieldError('validation', 'email', 'INVALID');
        $payload = new Payload(422, null, $fieldError);

        $result = $payload->getErrors();
        $this->assertCount(1, $result);
        $this->assertSame($fieldError, $result[0]);
    }

    public function testGetWarningsReturnsNullWhenNotSet(): void
    {
        $payload = new Payload();
        $this->assertNull($payload->getWarnings());
    }

    public function testGetWarningsWrapsSingleWarningInArray(): void
    {
        $warning = new Warning('deprecation', 'Use /v2.');
        $payload = new Payload(200, ['data'], null, $warning);

        $result = $payload->getWarnings();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($warning, $result[0]);
    }

    public function testGetWarningsPassesThroughArray(): void
    {
        $warnings = [new Warning('notice'), new Warning('deprecation')];
        $payload = new Payload(200, null, null, $warnings);

        $result = $payload->getWarnings();
        $this->assertSame($warnings, $result);
    }

    public function testJsonSerializeWithDataOnly(): void
    {
        $payload = new Payload(200, ['key' => 'value']);

        $this->assertSame(['data' => ['key' => 'value']], $payload->jsonSerialize());
    }

    public function testJsonSerializeWithErrorsOnly(): void
    {
        $error = new Error('not_found');
        $payload = new Payload(404, null, $error);

        $result = $payload->jsonSerialize();
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayNotHasKey('data', $result);
        $this->assertCount(1, $result['errors']);
    }

    public function testJsonSerializeDataTakesPrecedenceOverErrors(): void
    {
        $error = new Error('some_error');
        $payload = new Payload(200, ['result' => true], $error);

        $result = $payload->jsonSerialize();
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    public function testJsonSerializeWithWarnings(): void
    {
        $warning = new Warning('deprecation');
        $payload = new Payload(200, ['ok' => true], null, $warning);

        $result = $payload->jsonSerialize();
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertCount(1, $result['warnings']);
    }

    public function testJsonSerializeEmptyPayload(): void
    {
        $payload = new Payload();

        $this->assertSame([], $payload->jsonSerialize());
    }

    public function testJsonSerializeWarningsWithoutDataOrErrors(): void
    {
        $warning = new Warning('notice', 'Something to note.');
        $payload = new Payload(200, null, null, $warning);

        $result = $payload->jsonSerialize();
        $this->assertArrayNotHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
    }
}

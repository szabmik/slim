<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\Error;
use Szabmik\Slim\Action\Error\FieldError;
use Szabmik\Slim\Action\Payload;
use Szabmik\Slim\Action\Warning;

/**
 * Unit tests for Payload class.
 */
class PayloadTest extends TestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $payload = new Payload();

        $this->assertSame(200, $payload->getStatusCode());
        $this->assertNull($payload->getData());
        $this->assertNull($payload->getErrors());
        $this->assertNull($payload->getWarnings());
    }

    public function testConstructorWithStatusCode(): void
    {
        $payload = new Payload(201);

        $this->assertSame(201, $payload->getStatusCode());
    }

    public function testConstructorWithData(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $payload = new Payload(200, $data);

        $this->assertSame($data, $payload->getData());
    }

    public function testConstructorWithObjectData(): void
    {
        $data = (object)['id' => 1, 'name' => 'Test'];
        $payload = new Payload(200, $data);

        $this->assertSame($data, $payload->getData());
    }

    public function testConstructorWithSingleError(): void
    {
        $error = new Error('TEST_ERROR', 'Test description');
        $payload = new Payload(400, null, $error);

        $errors = $payload->getErrors();
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
        $this->assertSame($error, $errors[0]);
    }

    public function testConstructorWithMultipleErrors(): void
    {
        $errors = [
            new Error('ERROR_1', 'Description 1'),
            new Error('ERROR_2', 'Description 2'),
        ];
        $payload = new Payload(400, null, $errors);

        $result = $payload->getErrors();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($errors, $result);
    }

    public function testConstructorWithSingleWarning(): void
    {
        $warning = new Warning('DEPRECATION', 'Feature deprecated');
        $payload = new Payload(200, ['test' => 'data'], null, $warning);

        $warnings = $payload->getWarnings();
        $this->assertIsArray($warnings);
        $this->assertCount(1, $warnings);
        $this->assertSame($warning, $warnings[0]);
    }

    public function testConstructorWithMultipleWarnings(): void
    {
        $warnings = [
            new Warning('DEPRECATION', 'Feature deprecated'),
            new Warning('NOTICE', 'Something to note'),
        ];
        $payload = new Payload(200, ['test' => 'data'], null, $warnings);

        $result = $payload->getWarnings();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($warnings, $result);
    }

    public function testJsonSerializeWithData(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $payload = new Payload(200, $data);
        $json = $payload->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayNotHasKey('errors', $json);
        $this->assertArrayNotHasKey('warnings', $json);
        $this->assertSame($data, $json['data']);
    }

    public function testJsonSerializeWithErrors(): void
    {
        $error = new Error('TEST_ERROR', 'Description');
        $payload = new Payload(400, null, $error);
        $json = $payload->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayNotHasKey('data', $json);
        $this->assertIsArray($json['errors']);
        $this->assertCount(1, $json['errors']);
    }

    public function testJsonSerializeWithDataAndWarnings(): void
    {
        $data = ['id' => 1];
        $warning = new Warning('NOTICE', 'Note this');
        $payload = new Payload(200, $data, null, $warning);
        $json = $payload->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('warnings', $json);
        $this->assertArrayNotHasKey('errors', $json);
        $this->assertSame($data, $json['data']);
        $this->assertIsArray($json['warnings']);
        $this->assertCount(1, $json['warnings']);
    }

    public function testJsonSerializeWithErrorsAndWarnings(): void
    {
        $error = new Error('TEST_ERROR');
        $warning = new Warning('NOTICE');
        $payload = new Payload(400, null, $error, $warning);
        $json = $payload->jsonSerialize();

        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('warnings', $json);
        $this->assertArrayNotHasKey('data', $json);
    }

    public function testJsonEncode(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $payload = new Payload(200, $data);
        $jsonString = json_encode($payload);

        $this->assertIsString($jsonString);
        $decoded = json_decode($jsonString, true);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame($data, $decoded['data']);
    }

    public function testGetErrorsNormalizesArray(): void
    {
        $errors = [
            new Error('ERROR_1'),
            new Error('ERROR_2'),
        ];
        $payload = new Payload(400, null, $errors);

        $result = $payload->getErrors();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetWarningsNormalizesArray(): void
    {
        $warnings = [
            new Warning('WARNING_1'),
            new Warning('WARNING_2'),
        ];
        $payload = new Payload(200, ['test'], null, $warnings);

        $result = $payload->getWarnings();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testMixedErrorTypes(): void
    {
        $errors = [
            new Error('GENERAL_ERROR', 'General error'),
            new FieldError('VALIDATION_ERROR', 'email', 'INVALID', 'Invalid email'),
        ];
        $payload = new Payload(400, null, $errors);

        $result = $payload->getErrors();
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Error::class, $result[0]);
        $this->assertInstanceOf(FieldError::class, $result[1]);
    }

    public function testEmptyPayloadJsonSerialize(): void
    {
        $payload = new Payload();
        $json = $payload->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertEmpty($json);
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\SchemaValidator\ISchemaValidator;
use Szabmik\Slim\SchemaValidator\SchemaValidationResult;
use Szabmik\Slim\SchemaValidator\SchemaValidator;

class SchemaValidatorTest extends TestCase
{
    private string $schemaFolder;

    protected function setUp(): void
    {
        $this->schemaFolder = sys_get_temp_dir() . '/slim_validator_test_' . uniqid();
        mkdir($this->schemaFolder, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->schemaFolder . '/*.json') as $file) {
            unlink($file);
        }
        rmdir($this->schemaFolder);
    }

    public function testImplementsISchemaValidator(): void
    {
        $validator = new SchemaValidator($this->schemaFolder);
        $this->assertInstanceOf(ISchemaValidator::class, $validator);
    }

    public function testValidateReturnsSchemaValidationResult(): void
    {
        $validator = new SchemaValidator($this->schemaFolder);
        $result = $validator->validate((object) [], (object) ['type' => 'object']);

        $this->assertInstanceOf(SchemaValidationResult::class, $result);
    }

    public function testValidDataIsValid(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'name' => (object) ['type' => 'string'],
            ],
            'required' => ['name'],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) ['name' => 'John'], $schema);

        $this->assertTrue($result->isValid);
    }

    public function testInvalidDataIsNotValid(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'name' => (object) ['type' => 'string'],
            ],
            'required' => ['name'],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) [], $schema);

        $this->assertFalse($result->isValid);
    }

    public function testErrorsEmptyWhenValid(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'age' => (object) ['type' => 'integer'],
            ],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) ['age' => 25], $schema);

        $this->assertSame([], $result->errors);
    }

    public function testErrorsNotEmptyWhenInvalid(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'email' => (object) ['type' => 'string'],
            ],
            'required' => ['email'],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) [], $schema);

        $this->assertNotEmpty($result->errors);
    }

    public function testErrorsContainKeywordAndMessage(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'name' => (object) ['type' => 'string'],
            ],
            'required' => ['name'],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) [], $schema);

        $errors = $result->errors;
        $firstErrorGroup = reset($errors);
        $firstError = $firstErrorGroup[0] ?? $firstErrorGroup;

        $this->assertArrayHasKey('keyword', $firstError);
        $this->assertArrayHasKey('message', $firstError);
    }

    public function testTypeValidationError(): void
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'age' => (object) ['type' => 'integer'],
            ],
        ];

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) ['age' => 'not-a-number'], $schema);

        $this->assertFalse($result->isValid);
        $this->assertNotEmpty($result->errors);
    }

    public function testValidateWithJsonStringSchema(): void
    {
        $schema = json_encode([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
        ]);

        $result = (new SchemaValidator($this->schemaFolder))->validate((object) ['name' => 'test'], $schema);

        $this->assertTrue($result->isValid);
    }
}

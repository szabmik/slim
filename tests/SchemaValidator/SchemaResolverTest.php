<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\SchemaValidator\Exception\InvalidJSONSchema;
use Szabmik\Slim\SchemaValidator\Exception\JSONSchemaDoesNotExist;
use Szabmik\Slim\SchemaValidator\ISchemaResolver;
use Szabmik\Slim\SchemaValidator\SchemaResolver;

/**
 * Unit tests for SchemaResolver class.
 */
class SchemaResolverTest extends TestCase
{
    private string $testSchemaFolder;
    private SchemaResolver $resolver;

    protected function setUp(): void
    {
        $this->testSchemaFolder = sys_get_temp_dir() . '/test_schemas_' . uniqid();
        mkdir($this->testSchemaFolder, 0777, true);
        $this->resolver = new SchemaResolver($this->testSchemaFolder);
    }

    protected function tearDown(): void
    {
        // Clean up test schemas
        $files = glob($this->testSchemaFolder . '/*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->testSchemaFolder)) {
            rmdir($this->testSchemaFolder);
        }
    }

    public function testImplementsISchemaResolver(): void
    {
        $this->assertInstanceOf(ISchemaResolver::class, $this->resolver);
    }

    public function testResolveValidSchema(): void
    {
        $schemaContent = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
        ];
        file_put_contents(
            $this->testSchemaFolder . '/user.json',
            json_encode($schemaContent)
        );

        $result = $this->resolver->resolve('user');

        $this->assertIsObject($result);
        $this->assertSame('object', $result->type);
        $this->assertIsObject($result->properties);
    }

    public function testResolveThrowsExceptionWhenSchemaDoesNotExist(): void
    {
        $this->expectException(JSONSchemaDoesNotExist::class);
        $this->expectExceptionMessage('JSON schema does not exist. (`nonexistent`)');

        $this->resolver->resolve('nonexistent');
    }

    public function testResolveThrowsExceptionForInvalidJSON(): void
    {
        file_put_contents(
            $this->testSchemaFolder . '/invalid.json',
            'This is not valid JSON {'
        );

        $this->expectException(InvalidJSONSchema::class);
        $this->expectExceptionMessage('JSON schema cannot be decoded. (`invalid`)');

        $this->resolver->resolve('invalid');
    }

    public function testResolveWithComplexSchema(): void
    {
        $schemaContent = [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'type' => 'object',
            'required' => ['id', 'name'],
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string', 'minLength' => 1],
                'email' => ['type' => 'string', 'format' => 'email'],
                'age' => ['type' => 'integer', 'minimum' => 0],
            ],
        ];
        file_put_contents(
            $this->testSchemaFolder . '/complex.json',
            json_encode($schemaContent)
        );

        $result = $this->resolver->resolve('complex');

        $this->assertIsObject($result);
        $this->assertSame('object', $result->type);
        $this->assertIsArray($result->required);
        $this->assertContains('id', $result->required);
        $this->assertContains('name', $result->required);
    }

    public function testResolveWithEmptyJSONThrowsException(): void
    {
        file_put_contents($this->testSchemaFolder . '/empty.json', '');

        $this->expectException(InvalidJSONSchema::class);
        $this->expectExceptionMessage('JSON schema cannot be decoded. (`empty`)');

        $this->resolver->resolve('empty');
    }

    public function testResolveMultipleSchemasFromSameFolder(): void
    {
        $schema1 = ['type' => 'object'];
        $schema2 = ['type' => 'array'];

        file_put_contents(
            $this->testSchemaFolder . '/schema1.json',
            json_encode($schema1)
        );
        file_put_contents(
            $this->testSchemaFolder . '/schema2.json',
            json_encode($schema2)
        );

        $result1 = $this->resolver->resolve('schema1');
        $result2 = $this->resolver->resolve('schema2');

        $this->assertSame('object', $result1->type);
        $this->assertSame('array', $result2->type);
    }
}

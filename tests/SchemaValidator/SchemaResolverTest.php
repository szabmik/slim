<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator;

use JsonException;
use PHPUnit\Framework\TestCase;
use Szabmik\Slim\SchemaValidator\Exception\JsonSchemaDoesNotExist;
use Szabmik\Slim\SchemaValidator\SchemaResolver;

class SchemaResolverTest extends TestCase
{
    private string $schemaFolder;

    protected function setUp(): void
    {
        $this->schemaFolder = sys_get_temp_dir() . '/slim_schema_test_' . uniqid();
        mkdir($this->schemaFolder);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->schemaFolder . '/*.json') as $file) {
            unlink($file);
        }
        rmdir($this->schemaFolder);
    }

    public function testResolvesValidSchema(): void
    {
        $schema = ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]];
        file_put_contents($this->schemaFolder . '/user.json', json_encode($schema));

        $resolver = new SchemaResolver($this->schemaFolder);
        $result = $resolver->resolve('user');

        $this->assertIsObject($result);
        $this->assertSame('object', $result->type);
    }

    public function testResolvedSchemaContainsNestedProperties(): void
    {
        $schema = ['type' => 'object', 'required' => ['email']];
        file_put_contents($this->schemaFolder . '/request.json', json_encode($schema));

        $resolver = new SchemaResolver($this->schemaFolder);
        $result = $resolver->resolve('request');

        $this->assertSame(['email'], $result->required);
    }

    public function testThrowsJsonSchemaDoesNotExistForMissingFile(): void
    {
        $resolver = new SchemaResolver($this->schemaFolder);

        $this->expectException(JsonSchemaDoesNotExist::class);
        $this->expectExceptionMessage('JSON schema does not exist. (`nonexistent`)');

        $resolver->resolve('nonexistent');
    }

    public function testThrowsJsonExceptionForInvalidJson(): void
    {
        file_put_contents($this->schemaFolder . '/broken.json', '{not valid json');

        $resolver = new SchemaResolver($this->schemaFolder);

        $this->expectException(JsonException::class);

        $resolver->resolve('broken');
    }

    public function testThrowsJsonSchemaDoesNotExistForNonexistentFolder(): void
    {
        $resolver = new SchemaResolver('/nonexistent/path/that/does/not/exist');

        $this->expectException(JsonSchemaDoesNotExist::class);

        $resolver->resolve('schema');
    }
}

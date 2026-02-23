<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Szabmik\Slim\Attributes\ValidateSchema;
use Szabmik\Slim\Attributes\ValidationType;

class ValidateSchemaTest extends TestCase
{
    public function testRequestBodyEnumValue(): void
    {
        $this->assertSame('requestBody', ValidationType::RequestBody->value);
    }

    public function testQueryParametersEnumValue(): void
    {
        $this->assertSame('queryParameters', ValidationType::QueryParameters->value);
    }

    public function testConstructorSetsType(): void
    {
        $attribute = new ValidateSchema(ValidationType::RequestBody, 'CreateUser');
        $this->assertSame(ValidationType::RequestBody, $attribute->type);
    }

    public function testConstructorSetsSchemaName(): void
    {
        $attribute = new ValidateSchema(ValidationType::RequestBody, 'CreateUser');
        $this->assertSame('CreateUser', $attribute->schemaName);
    }

    public function testIsPhpAttribute(): void
    {
        $reflection = new ReflectionClass(ValidateSchema::class);
        $attributes = $reflection->getAttributes();

        $this->assertNotEmpty($attributes);
        $this->assertSame('Attribute', $attributes[0]->getName());
    }

    public function testCanBeInstantiatedWithQueryParametersType(): void
    {
        $attribute = new ValidateSchema(ValidationType::QueryParameters, 'ListUsers');

        $this->assertSame(ValidationType::QueryParameters, $attribute->type);
        $this->assertSame('ListUsers', $attribute->schemaName);
    }
}

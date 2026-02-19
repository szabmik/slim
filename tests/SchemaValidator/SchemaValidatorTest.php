<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\SchemaValidator;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\SchemaValidator\ISchemaValidator;
use Szabmik\Slim\SchemaValidator\SchemaValidator;

/**
 * Unit tests for SchemaValidator class.
 */
class SchemaValidatorTest extends TestCase
{
    private string $testSchemaFolder;
    private SchemaValidator $validator;

    protected function setUp(): void
    {
        $this->testSchemaFolder = sys_get_temp_dir() . '/test_validator_schemas_' . uniqid();
        mkdir($this->testSchemaFolder, 0777, true);
        $this->validator = new SchemaValidator($this->testSchemaFolder);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testSchemaFolder)) {
            rmdir($this->testSchemaFolder);
        }
    }

    public function testImplementsISchemaValidator(): void
    {
        $this->assertInstanceOf(ISchemaValidator::class, $this->validator);
    }

    public function testValidateWithValidData(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'name' => (object)['type' => 'string'],
                'age' => (object)['type' => 'integer'],
            ],
        ];

        $data = (object)[
            'name' => 'John Doe',
            'age' => 30,
        ];

        $this->validator->validate($data, $schema);

        $this->assertTrue($this->validator->isValid());
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testValidateWithInvalidData(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'name' => (object)['type' => 'string'],
                'age' => (object)['type' => 'integer'],
            ],
        ];

        $data = (object)[
            'name' => 'John Doe',
            'age' => 'not a number',
        ];

        $this->validator->validate($data, $schema);

        $this->assertFalse($this->validator->isValid());
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function testValidateWithRequiredFields(): void
    {
        $schema = (object)[
            'type' => 'object',
            'required' => ['name', 'email'],
            'properties' => (object)[
                'name' => (object)['type' => 'string'],
                'email' => (object)['type' => 'string'],
            ],
        ];

        $data = (object)[
            'name' => 'John Doe',
        ];

        $this->validator->validate($data, $schema);

        $this->assertFalse($this->validator->isValid());
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
    }

    public function testValidateWithJSONString(): void
    {
        $schema = '{"type": "object", "properties": {"name": {"type": "string"}}}';
        $data = '{"name": "John Doe"}';

        $this->validator->validate(json_decode($data), json_decode($schema));

        $this->assertTrue($this->validator->isValid());
    }

    public function testValidateWithInvalidJSONString(): void
    {
        $schema = '{"type": "object", "properties": {"age": {"type": "integer"}}}';
        $data = '{"age": "not a number"}';

        $this->validator->validate($data, $schema);

        $this->assertFalse($this->validator->isValid());
    }

    public function testGetErrorsReturnsStructuredErrors(): void
    {
        $schema = (object)[
            'type' => 'object',
            'required' => ['name'],
            'properties' => (object)[
                'name' => (object)['type' => 'string'],
            ],
        ];

        // Missing required field
        $data = (object)[];

        $this->validator->validate($data, $schema);
        $errors = $this->validator->getErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    public function testValidateWithNestedObjects(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'user' => (object)[
                    'type' => 'object',
                    'properties' => (object)[
                        'name' => (object)['type' => 'string'],
                    ],
                ],
            ],
        ];

        $validData = (object)[
            'user' => (object)[
                'name' => 'John',
            ],
        ];

        $this->validator->validate($validData, $schema);
        $this->assertTrue($this->validator->isValid());
    }

    public function testValidateWithMinMaxConstraints(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'age' => (object)[
                    'type' => 'integer',
                    'minimum' => 0,
                    'maximum' => 120,
                ],
            ],
        ];

        $validData = (object)['age' => 30];
        $this->validator->validate($validData, $schema);
        $this->assertTrue($this->validator->isValid());

        $invalidData = (object)['age' => -5];
        $this->validator->validate($invalidData, $schema);
        $this->assertFalse($this->validator->isValid());
    }

    public function testValidateWithPrefix(): void
    {
        $validator = new SchemaValidator($this->testSchemaFolder, 'http://example.com/schemas/');

        $schema = (object)[
            'type' => 'string',
        ];

        $this->validator->validate('test', $schema);
        $this->assertTrue($this->validator->isValid());
    }

    public function testValidateComplexNestedObject(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'user' => (object)[
                    'type' => 'object',
                    'properties' => (object)[
                        'name' => (object)['type' => 'string'],
                        'email' => (object)['type' => 'string'],
                    ],
                    'required' => ['name'],
                ],
            ],
        ];

        $validData = (object)[
            'user' => (object)[
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        $this->validator->validate($validData, $schema);
        $this->assertTrue($this->validator->isValid());

        $invalidData = (object)[
            'user' => (object)[
                'email' => 'john@example.com',
            ],
        ];

        $this->validator->validate($invalidData, $schema);
        $this->assertFalse($this->validator->isValid());
    }

    public function testMultipleValidationCalls(): void
    {
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'value' => (object)['type' => 'string'],
            ],
        ];

        // First validation - valid
        $validData = (object)['value' => 'test'];
        $this->validator->validate($validData, $schema);
        $this->assertTrue($this->validator->isValid());

        // Second validation - invalid
        $invalidData = (object)['value' => 123];
        $this->validator->validate($invalidData, $schema);
        $this->assertFalse($this->validator->isValid());

        // Third validation - valid again
        $validData2 = (object)['value' => 'another test'];
        $this->validator->validate($validData2, $schema);
        $this->assertTrue($this->validator->isValid());
    }
}

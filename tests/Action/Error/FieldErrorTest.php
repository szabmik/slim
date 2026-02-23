<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Error;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\Error;
use Szabmik\Slim\Action\Error\FieldError;

class FieldErrorTest extends TestCase
{
    public function testExtendsError(): void
    {
        $fieldError = new FieldError('validation', 'email', 'INVALID_FORMAT');
        $this->assertInstanceOf(Error::class, $fieldError);
    }

    public function testConstructorSetsAllFields(): void
    {
        $fieldError = new FieldError('validation', 'email', 'INVALID_FORMAT', 'Invalid email address.', 'uid-001');

        $this->assertSame('validation', $fieldError->getType());
        $this->assertSame('email', $fieldError->getFieldName());
        $this->assertSame('INVALID_FORMAT', $fieldError->getCode());
        $this->assertSame('Invalid email address.', $fieldError->getDescription());
        $this->assertSame('uid-001', $fieldError->getUid());
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $fieldError = new FieldError('validation', 'name', 'REQUIRED');
        $this->assertNull($fieldError->getDescription());
    }

    public function testUidDefaultsToNull(): void
    {
        $fieldError = new FieldError('validation', 'name', 'REQUIRED');
        $this->assertNull($fieldError->getUid());
    }

    public function testJsonSerializeIncludesAllFields(): void
    {
        $fieldError = new FieldError('validation', 'email', 'INVALID_FORMAT', 'Bad email.', 'uid-x');

        $this->assertSame([
            'type'        => 'validation',
            'description' => 'Bad email.',
            'uid'         => 'uid-x',
            'code'        => 'INVALID_FORMAT',
            'fieldName'   => 'email',
        ], $fieldError->jsonSerialize());
    }

    public function testJsonSerializeWithNullOptionals(): void
    {
        $fieldError = new FieldError('validation', 'username', 'TOO_SHORT');

        $this->assertSame([
            'type'        => 'validation',
            'description' => null,
            'uid'         => null,
            'code'        => 'TOO_SHORT',
            'fieldName'   => 'username',
        ], $fieldError->jsonSerialize());
    }
}

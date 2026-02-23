<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Error;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Error\Error;

class ErrorTest extends TestCase
{
    public function testConstructorSetsType(): void
    {
        $error = new Error('validation_error');
        $this->assertSame('validation_error', $error->getType());
    }

    public function testConstructorSetsDescription(): void
    {
        $error = new Error('not_found', 'Resource was not found.');
        $this->assertSame('Resource was not found.', $error->getDescription());
    }

    public function testConstructorSetsUid(): void
    {
        $error = new Error('server_error', null, 'uid-123');
        $this->assertSame('uid-123', $error->getUid());
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $error = new Error('type');
        $this->assertNull($error->getDescription());
    }

    public function testUidDefaultsToNull(): void
    {
        $error = new Error('type');
        $this->assertNull($error->getUid());
    }

    public function testJsonSerializeIncludesAllFields(): void
    {
        $error = new Error('auth_error', 'Token expired.', 'uid-789');

        $this->assertSame([
            'type'        => 'auth_error',
            'description' => 'Token expired.',
            'uid'         => 'uid-789',
        ], $error->jsonSerialize());
    }

    public function testJsonSerializeWithNullOptionals(): void
    {
        $error = new Error('generic_error');

        $this->assertSame([
            'type'        => 'generic_error',
            'description' => null,
            'uid'         => null,
        ], $error->jsonSerialize());
    }
}

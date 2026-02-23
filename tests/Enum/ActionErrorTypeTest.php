<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Enum\ActionErrorType;

class ActionErrorTypeTest extends TestCase
{
    public function testBadRequestValue(): void
    {
        $this->assertSame('BAD_REQUEST', ActionErrorType::BAD_REQUEST->value);
    }

    public function testInsufficientPrivilegesValue(): void
    {
        $this->assertSame('INSUFFICIENT_PRIVILEGES', ActionErrorType::INSUFFICIENT_PRIVILEGES->value);
    }

    public function testNotAllowedValue(): void
    {
        $this->assertSame('NOT_ALLOWED', ActionErrorType::NOT_ALLOWED->value);
    }

    public function testNotImplementedValue(): void
    {
        $this->assertSame('NOT_IMPLEMENTED', ActionErrorType::NOT_IMPLEMENTED->value);
    }

    public function testResourceNotFoundValue(): void
    {
        $this->assertSame('RESOURCE_NOT_FOUND', ActionErrorType::RESOURCE_NOT_FOUND->value);
    }

    public function testServerErrorValue(): void
    {
        $this->assertSame('SERVER_ERROR', ActionErrorType::SERVER_ERROR->value);
    }

    public function testUnauthenticatedValue(): void
    {
        $this->assertSame('UNAUTHENTICATED', ActionErrorType::UNAUTHENTICATED->value);
    }

    public function testValidationErrorValue(): void
    {
        $this->assertSame('VALIDATION_ERROR', ActionErrorType::VALIDATION_ERROR->value);
    }

    public function testSchemaValidationErrorValue(): void
    {
        $this->assertSame('SCHEMA_VALIDATION_ERROR', ActionErrorType::SCHEMA_VALIDATION_ERROR->value);
    }

    public function testVerificationErrorValue(): void
    {
        $this->assertSame('VERIFICATION_ERROR', ActionErrorType::VERIFICATION_ERROR->value);
    }

    public function testTotalCaseCount(): void
    {
        $this->assertCount(10, ActionErrorType::cases());
    }

    public function testFromStringValue(): void
    {
        $this->assertSame(ActionErrorType::SERVER_ERROR, ActionErrorType::from('SERVER_ERROR'));
    }

    public function testTryFromInvalidValueReturnsNull(): void
    {
        $this->assertNull(ActionErrorType::tryFrom('NONEXISTENT'));
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Enum\ServiceStatus;

class ServiceStatusTest extends TestCase
{
    public function testHealthyValue(): void
    {
        $this->assertSame('healthy', ServiceStatus::Healthy->value);
    }

    public function testUnhealthyValue(): void
    {
        $this->assertSame('unhealthy', ServiceStatus::Unhealthy->value);
    }

    public function testTotalCaseCount(): void
    {
        $this->assertCount(2, ServiceStatus::cases());
    }

    public function testFromStringValue(): void
    {
        $this->assertSame(ServiceStatus::Healthy, ServiceStatus::from('healthy'));
        $this->assertSame(ServiceStatus::Unhealthy, ServiceStatus::from('unhealthy'));
    }

    public function testTryFromInvalidValueReturnsNull(): void
    {
        $this->assertNull(ServiceStatus::tryFrom('unknown'));
    }
}

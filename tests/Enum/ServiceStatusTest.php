<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Enum\ServiceStatus;

/**
 * Unit tests for ServiceStatus enum.
 */
class ServiceStatusTest extends TestCase
{
    public function testHealthyCaseValue(): void
    {
        $this->assertSame('healthy', ServiceStatus::Healthy->value);
    }

    public function testUnhealthyCaseValue(): void
    {
        $this->assertSame('unhealthy', ServiceStatus::Unhealthy->value);
    }

    public function testEnumCases(): void
    {
        $cases = ServiceStatus::cases();
        $this->assertCount(2, $cases);
        $this->assertContains(ServiceStatus::Healthy, $cases);
        $this->assertContains(ServiceStatus::Unhealthy, $cases);
    }

    public function testFromString(): void
    {
        $this->assertSame(ServiceStatus::Healthy, ServiceStatus::from('healthy'));
        $this->assertSame(ServiceStatus::Unhealthy, ServiceStatus::from('unhealthy'));
    }

    public function testTryFromValidValue(): void
    {
        $this->assertSame(ServiceStatus::Healthy, ServiceStatus::tryFrom('healthy'));
        $this->assertSame(ServiceStatus::Unhealthy, ServiceStatus::tryFrom('unhealthy'));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(ServiceStatus::tryFrom('invalid'));
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\ServiceStatus;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\ServiceStatus\IReadinessCheck;
use Szabmik\Slim\Action\ServiceStatus\ReadinessCheckRegistry;

class ReadinessCheckRegistryTest extends TestCase
{
    public function testAllReturnsEmptyArrayByDefault(): void
    {
        $registry = new ReadinessCheckRegistry();
        $this->assertSame([], $registry->all());
    }

    public function testRegisterAddsCheck(): void
    {
        $registry = new ReadinessCheckRegistry();
        $check = $this->createMock(IReadinessCheck::class);

        $registry->register($check);

        $this->assertCount(1, $registry->all());
        $this->assertSame($check, $registry->all()[0]);
    }

    public function testRegisterMultipleChecks(): void
    {
        $registry = new ReadinessCheckRegistry();
        $check1 = $this->createMock(IReadinessCheck::class);
        $check2 = $this->createMock(IReadinessCheck::class);
        $check3 = $this->createMock(IReadinessCheck::class);

        $registry->register($check1);
        $registry->register($check2);
        $registry->register($check3);

        $this->assertCount(3, $registry->all());
        $this->assertSame($check1, $registry->all()[0]);
        $this->assertSame($check2, $registry->all()[1]);
        $this->assertSame($check3, $registry->all()[2]);
    }

    public function testRegisteredChecksPreserveOrder(): void
    {
        $registry = new ReadinessCheckRegistry();

        $checkA = $this->createMock(IReadinessCheck::class);
        $checkA->method('getName')->willReturn('database');

        $checkB = $this->createMock(IReadinessCheck::class);
        $checkB->method('getName')->willReturn('redis');

        $registry->register($checkA);
        $registry->register($checkB);

        $checks = $registry->all();
        $this->assertSame('database', $checks[0]->getName());
        $this->assertSame('redis', $checks[1]->getName());
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\ServiceStatus;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\ServiceStatus\IReadinessCheck;
use Szabmik\Slim\Action\ServiceStatus\ReadinessCheckRegistry;

/**
 * Unit tests for ReadinessCheckRegistry class.
 */
class ReadinessCheckRegistryTest extends TestCase
{
    private ReadinessCheckRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ReadinessCheckRegistry();
    }

    public function testRegisterAddsCheckToRegistry(): void
    {
        $check = $this->createMock(IReadinessCheck::class);

        $this->registry->register($check);

        $checks = $this->registry->all();
        $this->assertCount(1, $checks);
        $this->assertSame($check, $checks[0]);
    }

    public function testRegisterMultipleChecks(): void
    {
        $check1 = $this->createMock(IReadinessCheck::class);
        $check2 = $this->createMock(IReadinessCheck::class);
        $check3 = $this->createMock(IReadinessCheck::class);

        $this->registry->register($check1);
        $this->registry->register($check2);
        $this->registry->register($check3);

        $checks = $this->registry->all();
        $this->assertCount(3, $checks);
        $this->assertSame($check1, $checks[0]);
        $this->assertSame($check2, $checks[1]);
        $this->assertSame($check3, $checks[2]);
    }

    public function testAllReturnsEmptyArrayInitially(): void
    {
        $checks = $this->registry->all();

        $this->assertIsArray($checks);
        $this->assertEmpty($checks);
    }

    public function testAllReturnsAllRegisteredChecks(): void
    {
        $check1 = $this->createMock(IReadinessCheck::class);
        $check2 = $this->createMock(IReadinessCheck::class);

        $this->registry->register($check1);
        $this->registry->register($check2);

        $checks = $this->registry->all();

        $this->assertCount(2, $checks);
        $this->assertContains($check1, $checks);
        $this->assertContains($check2, $checks);
    }

    public function testRegisterPreservesOrder(): void
    {
        $checks = [];
        for ($i = 0; $i < 5; $i++) {
            $check = $this->createMock(IReadinessCheck::class);
            $checks[] = $check;
            $this->registry->register($check);
        }

        $registeredChecks = $this->registry->all();

        for ($i = 0; $i < 5; $i++) {
            $this->assertSame($checks[$i], $registeredChecks[$i]);
        }
    }

    public function testRegisterSameCheckMultipleTimes(): void
    {
        $check = $this->createMock(IReadinessCheck::class);

        $this->registry->register($check);
        $this->registry->register($check);
        $this->registry->register($check);

        $checks = $this->registry->all();
        $this->assertCount(3, $checks);
    }

    public function testAllReturnsArrayOfIReadinessCheck(): void
    {
        $check1 = $this->createMock(IReadinessCheck::class);
        $check2 = $this->createMock(IReadinessCheck::class);

        $this->registry->register($check1);
        $this->registry->register($check2);

        $checks = $this->registry->all();

        foreach ($checks as $check) {
            $this->assertInstanceOf(IReadinessCheck::class, $check);
        }
    }

    public function testRegistryWithConcreteImplementation(): void
    {
        $check = new class () implements IReadinessCheck {
            public function isReady(): bool
            {
                return true;
            }

            public function getName(): string
            {
                return 'test';
            }

            public function getDetails(): array
            {
                return ['status' => 'ok'];
            }

            public function isRequired(): bool
            {
                return true;
            }
        };

        $this->registry->register($check);

        $checks = $this->registry->all();
        $this->assertCount(1, $checks);
        $this->assertTrue($checks[0]->isReady());
        $this->assertSame('test', $checks[0]->getName());
    }
}

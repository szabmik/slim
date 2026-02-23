<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Warning;

class WarningTest extends TestCase
{
    public function testConstructorSetsType(): void
    {
        $warning = new Warning('deprecation');
        $this->assertSame('deprecation', $warning->getType());
    }

    public function testConstructorSetsDescription(): void
    {
        $warning = new Warning('notice', 'This endpoint will be removed.');
        $this->assertSame('This endpoint will be removed.', $warning->getDescription());
    }

    public function testConstructorSetsUid(): void
    {
        $warning = new Warning('deprecation', null, 'uid-w1');
        $this->assertSame('uid-w1', $warning->getUid());
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $warning = new Warning('deprecation');
        $this->assertNull($warning->getDescription());
    }

    public function testUidDefaultsToNull(): void
    {
        $warning = new Warning('deprecation');
        $this->assertNull($warning->getUid());
    }

    public function testJsonSerializeIncludesAllFields(): void
    {
        $warning = new Warning('deprecation', 'Use /v2 endpoint instead.', 'uid-w3');

        $this->assertSame([
            'type'        => 'deprecation',
            'description' => 'Use /v2 endpoint instead.',
            'uid'         => 'uid-w3',
        ], $warning->jsonSerialize());
    }

    public function testJsonSerializeWithNullOptionals(): void
    {
        $warning = new Warning('notice');

        $this->assertSame([
            'type'        => 'notice',
            'description' => null,
            'uid'         => null,
        ], $warning->jsonSerialize());
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Pagination;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Szabmik\Slim\Action\Pagination\Pagination;

class PaginationTest extends TestCase
{
    private function makeRequest(array $queryParams): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn($queryParams);

        return $request;
    }

    public function testDefaultsWhenQueryParamsAreEmpty(): void
    {
        $pagination = Pagination::fromRequest($this->makeRequest([]));

        $this->assertSame(20, $pagination->limit);
        $this->assertSame(0, $pagination->offset);
    }

    public function testExplicitLimitAndOffset(): void
    {
        $pagination = Pagination::fromRequest($this->makeRequest(['limit' => '10', 'offset' => '30']));

        $this->assertSame(10, $pagination->limit);
        $this->assertSame(30, $pagination->offset);
    }

    public function testCustomDefaultLimit(): void
    {
        $pagination = Pagination::fromRequest($this->makeRequest([]), defaultLimit: 50);

        $this->assertSame(50, $pagination->limit);
        $this->assertSame(0, $pagination->offset);
    }

    public function testLimitAtMaxLimitIsAllowed(): void
    {
        $pagination = Pagination::fromRequest($this->makeRequest(['limit' => '100']), maxLimit: 100);

        $this->assertSame(100, $pagination->limit);
    }

    public function testLimitExceedingMaxLimitThrows(): void
    {
        $this->expectException(HttpBadRequestException::class);

        Pagination::fromRequest($this->makeRequest(['limit' => '200']), maxLimit: 100);
    }

    public function testLimitBelowOneThrows(): void
    {
        $this->expectException(HttpBadRequestException::class);

        Pagination::fromRequest($this->makeRequest(['limit' => '0']));
    }

    public function testNegativeLimitThrows(): void
    {
        $this->expectException(HttpBadRequestException::class);

        Pagination::fromRequest($this->makeRequest(['limit' => '-1']));
    }

    public function testNegativeOffsetThrows(): void
    {
        $this->expectException(HttpBadRequestException::class);

        Pagination::fromRequest($this->makeRequest(['offset' => '-1']));
    }

    public function testZeroOffsetIsAllowed(): void
    {
        $pagination = Pagination::fromRequest($this->makeRequest(['offset' => '0']));

        $this->assertSame(0, $pagination->offset);
    }
}

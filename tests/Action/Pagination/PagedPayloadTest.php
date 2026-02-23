<?php

declare(strict_types=1);

namespace Szabmik\Slim\Tests\Action\Pagination;

use PHPUnit\Framework\TestCase;
use Szabmik\Slim\Action\Pagination\PagedPayload;
use Szabmik\Slim\Action\Pagination\Pagination;

class PagedPayloadTest extends TestCase
{
    private function makePagination(int $limit = 20, int $offset = 0): Pagination
    {
        return new Pagination($limit, $offset);
    }

    public function testJsonSerializeContainsDataAndPagination(): void
    {
        $payload = new PagedPayload($this->makePagination(20, 40), null, ['item1', 'item2']);

        $result = $payload->jsonSerialize();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testPaginationContainsLimitAndOffset(): void
    {
        $payload = new PagedPayload($this->makePagination(10, 30), null, []);

        $pagination = $payload->jsonSerialize()['pagination'];

        $this->assertSame(10, $pagination['limit']);
        $this->assertSame(30, $pagination['offset']);
    }

    public function testTotalIsIncludedWhenProvided(): void
    {
        $payload = new PagedPayload($this->makePagination(20, 0), 150, []);

        $pagination = $payload->jsonSerialize()['pagination'];

        $this->assertArrayHasKey('total', $pagination);
        $this->assertSame(150, $pagination['total']);
    }

    public function testTotalIsOmittedWhenNull(): void
    {
        $payload = new PagedPayload($this->makePagination(20, 0), null, []);

        $pagination = $payload->jsonSerialize()['pagination'];

        $this->assertArrayNotHasKey('total', $pagination);
    }

    public function testDataIsPreserved(): void
    {
        $items = [['id' => 1], ['id' => 2]];
        $payload = new PagedPayload($this->makePagination(), null, $items);

        $this->assertSame($items, $payload->jsonSerialize()['data']);
    }

    public function testDefaultStatusCodeIs200(): void
    {
        $payload = new PagedPayload($this->makePagination(), null);

        $this->assertSame(200, $payload->getStatusCode());
    }

    public function testCustomStatusCode(): void
    {
        $payload = new PagedPayload($this->makePagination(), null, null, 206);

        $this->assertSame(206, $payload->getStatusCode());
    }

    public function testFullResponseStructure(): void
    {
        $items = [['id' => 1]];
        $payload = new PagedPayload($this->makePagination(20, 40), 150, $items);

        $this->assertSame([
            'data'       => $items,
            'pagination' => [
                'limit'  => 20,
                'offset' => 40,
                'total'  => 150,
            ],
        ], $payload->jsonSerialize());
    }
}

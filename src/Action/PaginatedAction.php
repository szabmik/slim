<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use Psr\Http\Message\ResponseInterface;
use Szabmik\Slim\Action\Pagination\PagedPayload;
use Szabmik\Slim\Action\Pagination\Pagination;

abstract class PaginatedAction extends Action
{
    protected function getPagination(
        int $defaultLimit = 20,
        int $maxLimit = 100
    ): Pagination {
        return Pagination::fromRequest($this->request, $defaultLimit, $maxLimit);
    }

    /**
     * @param null|object|array<string, mixed> $items
     * @param Pagination $pagination
     * @param int|null $total
     * @param int $statusCode
     */
    protected function respondWithPagedData(
        null|object|array $items,
        Pagination $pagination,
        ?int $total = null,
        int $statusCode = 200
    ): ResponseInterface {
        $payload = new PagedPayload($pagination, $total, $items, $statusCode);

        return $this->respond($payload);
    }
}

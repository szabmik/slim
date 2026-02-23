<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class Pagination
{
    public function __construct(
        public readonly int $limit,
        public readonly int $offset,
    ) {
    }

    public static function fromRequest(
        ServerRequestInterface $request,
        int $defaultLimit = 20,
        int $maxLimit = 100
    ): self {
        $params = $request->getQueryParams();

        $limit  = isset($params['limit'])  ? (int) $params['limit']  : $defaultLimit;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;

        if ($limit < 1) {
            throw new HttpBadRequestException($request, 'Query parameter `limit` must be at least 1.');
        }

        if ($limit > $maxLimit) {
            throw new HttpBadRequestException($request, "Query parameter `limit` must not exceed {$maxLimit}.");
        }

        if ($offset < 0) {
            throw new HttpBadRequestException($request, 'Query parameter `offset` must not be negative.');
        }

        return new self($limit, $offset);
    }
}

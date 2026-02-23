<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Pagination;

use Szabmik\Slim\Action\Payload;

class PagedPayload extends Payload
{
    /**
     * @param Pagination $pagination
     * @param int|null $total
     * @param null|object|array<string, mixed> $data
     * @param int $statusCode
     */
    public function __construct(
        private readonly Pagination $pagination,
        private readonly ?int $total,
        null|object|array $data = null,
        int $statusCode = 200,
    ) {
        parent::__construct($statusCode, $data);
    }

    public function jsonSerialize(): array
    {
        $payload = parent::jsonSerialize();

        $paginationData = [
            'limit'  => $this->pagination->limit,
            'offset' => $this->pagination->offset,
        ];

        if ($this->total !== null) {
            $paginationData['total'] = $this->total;
        }

        $payload['pagination'] = $paginationData;

        return $payload;
    }
}

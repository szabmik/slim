<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use JsonSerializable;
use Szabmik\Slim\Action\Error\{Error, FieldError};

class Payload implements JsonSerializable
{
    public function __construct(
        private int $statusCode = 200,
        private $data = null,
        private array|null|Error|FieldError $error = null,
        private array|null|Warning $warning = null
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getError(): array|null|Error|FieldError
    {
        return $this->error;
    }

    public function getWarning(): array|null|Warning
    {
        return $this->warning;
    }

    public function jsonSerialize(): array
    {
        $payload = [];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload[is_array($this->error) ? 'errors' : 'error'] = $this->error;
        }

        if (!is_null($this->warning)) {
            $payload[is_array($this->warning) ? 'warnings' : 'warning'] = $this->warning;
        }

        return $payload;
    }
}

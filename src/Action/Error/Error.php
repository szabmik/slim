<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Error;

use JsonSerializable;

class Error implements JsonSerializable
{
    public function __construct(
        private string $type,
        private ?string $description = null
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): self
    {
        $this->description = $description;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
        ];
    }
}

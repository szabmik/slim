<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Error;

use JsonSerializable;

class FieldError implements JsonSerializable
{
    public function __construct(
        private string $type,
        private string $fieldName,
        private string $code,
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

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'code' => $this->code,
            'fieldName' => $this->fieldName,
            'description' => $this->description,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Error;

use JsonSerializable;

/**
 * Represents an error with a type identifier and optional description.
 *
 * Implements JsonSerializable to allow JSON encoding of the error details.
 */
class Error implements JsonSerializable
{
    /**
     * Initializes a new instance of the Error class.
     *
     * @param string $type The type or identifier of the error.
     * @param string|null $description Optional human-readable description of the error.
     */
    public function __construct(
        private readonly string $type,
        private readonly ?string $description = null,
        private readonly ?string $uid = null
    ) {
    }

    /**
     * Returns the type of the error.
     *
     * @return string The error type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the description of the error, if any.
     *
     * @return string|null The error description or null if not set.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns the unique identifier of the error, if any.
     *
     * @return string|null The error unique identifier or null if not set.
     */
    public function getUid(): ?string
    {
        return $this->uid;
    }

    /**
     * Specifies the data that should be serialized to JSON.
     *
     * @return array<string, string|null> An associative array containing 'type' and 'description'.
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
            'uid' => $this->uid
        ];
    }
}

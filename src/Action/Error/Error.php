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
        private string $type,
        private ?string $description = null,
        private ?string $uid = null
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
     * Sets the type of the error.
     *
     * @param string $type The new error type.
     *
     * @return self Returns the Error instance for method chaining.
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
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
     * Sets or updates the error description.
     *
     * @param string|null $description The new description.
     *
     * @return self Returns the Error instance for method chaining.
     */
    public function setDescription(?string $description = null): self
    {
        $this->description = $description;
        return $this;
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
     * Sets the unique identifier of the error.
     *
     * @param string|null $uid The new error unique identifier.
     *
     * @return self Returns the Error instance for method chaining.
     */
    public function setUid(?string $uid = null): self
    {
        $this->uid = $uid;
        return $this;
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

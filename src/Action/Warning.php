<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use JsonSerializable;

/**
 * Represents a non-critical warning within the response payload.
 *
 * A warning typically provides additional information that does not prevent request processing,
 * but may require attention. Implements JsonSerializable for consistent API output formatting.
 */
class Warning implements JsonSerializable
{
    /**
     * Initializes a new Warning instance.
     *
     * @param string $type Type or category of the warning (e.g. "deprecation", "notice").
     * @param string|null $description Optional human-readable explanation of the warning.
     */
    public function __construct(
        private string $type,
        private ?string $description = null,
        private ?string $uid = null
    ) {
    }

    /**
     * Gets the warning type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the warning type.
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gets the optional description of the warning.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets the warning description.
     *
     * @param string|null $description
     *
     * @return self
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
     * Converts the Warning instance into a serializable array for JSON output.
     *
     * @return array<string, string|null>
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

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
        private readonly string $type,
        private readonly ?string $description = null,
        private readonly ?string $uid = null
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
     * Gets the optional description of the warning.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns the unique identifier of the warning, if any.
     *
     * @return string|null The warning unique identifier or null if not set.
     */
    public function getUid(): ?string
    {
        return $this->uid;
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

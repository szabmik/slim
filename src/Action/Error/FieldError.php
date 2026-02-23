<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\Error;

/**
 * Represents a validation error related to a specific field.
 *
 * Extends the base Error class with field-specific details such as the field name and a unique error code.
 */
class FieldError extends Error
{
    /**
     * Creates a new FieldError instance.
     *
     * @param string $type (Inherited) The type of the error.
     * @param string $fieldName The name of the field where the error occurred.
     * @param string $code A machine-readable error code.
     * @param string|null $description (Inherited) An optional description of the error.
     */
    public function __construct(
        string $type,
        private readonly string $fieldName,
        private readonly string $code,
        ?string $description = null,
        ?string $uid = null
    ) {
        parent::__construct($type, $description, $uid);
    }

    /**
     * Gets the name of the affected field.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Gets the specific error code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns data for JSON serialization.
     *
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'code' => $this->code,
            'fieldName' => $this->fieldName,
        ]);
    }
}

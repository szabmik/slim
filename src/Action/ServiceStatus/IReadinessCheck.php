<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

/**
 * Interface for service readiness checks used in health monitoring.
 *
 * Implementations of this interface encapsulate the logic needed to determine
 * whether a specific application component (e.g., database, Redis, filesystem)
 * is ready to handle requests.
 *
 * These checks can be composed into a registry to support comprehensive readiness endpoints.
 */
interface IReadinessCheck
{
    /**
     * Indicates whether the component is currently ready for use.
     *
     * @return bool True if the component is healthy and operational.
     */
    public function isReady(): bool;

    /**
     * Gets the human-readable name of the component being checked.
     *
     * @return string The component identifier (e.g. "database", "redis").
     */
    public function getName(): string;

    /**
     * Provides diagnostic details about the component’s status.
     *
     * @return array<string, mixed> An associative array of status details.
     */
    public function getDetails(): array;

    /**
     * Indicates whether the component is required for the service to be considered ready.
     *
     * @return bool True if the component is required for the service to be considered ready.
     */
    public function isRequired(): bool;
}

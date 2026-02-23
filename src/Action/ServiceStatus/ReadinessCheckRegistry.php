<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

/**
 * Registry for collecting and managing readiness checks.
 *
 * This class acts as a container for IReadinessCheck implementations
 * and is typically used by a readiness action to evaluate the application's health.
 */
class ReadinessCheckRegistry
{
    /**
     * A list of registered readiness check objects.
     *
     * @var IReadinessCheck[]
     */
    private array $checks = [];

    /**
     * Registers a new readiness check.
     *
     * @param IReadinessCheck $check The check to register.
     */
    public function register(IReadinessCheck $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * Returns all registered readiness checks.
     *
     * @return IReadinessCheck[]
     */
    public function all(): array
    {
        return $this->checks;
    }
}

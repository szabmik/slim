<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Szabmik\Slim\Action\Action;
use Szabmik\Slim\Enum\ServiceStatus;

/**
 * Slim action that performs a comprehensive readiness check on registered components.
 *
 * Iterates through all registered IReadinessCheck implementations in the ReadinessCheckRegistry
 * and evaluates each component’s health. Returns an aggregated JSON response summarizing component-level
 * readiness, a global status, and the timestamp of evaluation.
 *
 * This action is commonly used as a Kubernetes readiness probe or other orchestrator-level check
 * to ensure that the application is fully operational before serving traffic.
 */
class Readiness extends Action
{
    /**
     * @param ReadinessCheckRegistry $registry Registry of readiness checks to evaluate.
     */
    public function __construct(private ReadinessCheckRegistry $registry)
    {
    }

    /**
     * Executes all readiness checks and returns a standardized JSON response.
     *
     * The response payload includes:
     * - overall service status (healthy/unhealthy)
     * - timestamp of the check
     * - per-component status and diagnostic details
     *
     * @return Response JSON response with Cache-Control set to no-store.
     */
    protected function action(): Response
    {
        $results = [];
        $allHealthy = true;

        foreach ($this->registry->all() as $check) {
            $ready = $check->isReady();
            $results[$check->getName()] = [
                'status' => $ready ? ServiceStatus::Healthy->value : ServiceStatus::Unhealthy->value,
                'details' => $check->getDetails(),
            ];
            $allHealthy = $allHealthy && $ready;
        }

        $status = $allHealthy ? ServiceStatus::Healthy->value : ServiceStatus::Unhealthy->value;

        return $this->respondWithData([
            'status' => $status,
            'checked_at' => (new DateTimeImmutable())->format('c'),
            'components' => $results,
        ])
            ->withHeader('Cache-Control', 'no-store');
    }
}

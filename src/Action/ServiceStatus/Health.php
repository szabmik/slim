<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Szabmik\Slim\Action\Action;
use Szabmik\Slim\Enum\ServiceStatus;

/**
 * Slim action to perform a basic health check on the application.
 *
 * Typically used for liveness or readiness probes in orchestration environments
 * (like Kubernetes) to confirm that the application is responsive and operational.
 *
 * Returns a simple 200 OK response with a minimal payload (e.g. `status: healthy`).
 */
class Health extends Action
{
    /**
     * Executes the health check and returns a 200 response with status confirmation.
     *
     * @return ResponseInterface
     */
    protected function action(): Response
    {
        $response = $this->respondWithData(
            [
                'status' => ServiceStatus::Healthy->value,
                'timestamp' => (new DateTimeImmutable())->format('c')
            ]
        );

        return $response->withHeader('Cache-Control', 'no-store');
    }
}

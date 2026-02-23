<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

use Psr\Http\Message\ResponseInterface as Response;
use Szabmik\Slim\Action\Action;

/**
 * Slim action to perform a basic liveness check on the application.
 *
 * Typically used for liveness probes in orchestration environments
 * (like Kubernetes) to confirm that the application is responsive and operational.
 *
 * Returns a 204 No Content response.
 */
final class Liveness extends Action
{
    /**
     * Executes the liveness check and returns a 204 response.
     *
     * @return ResponseInterface
     */
    protected function action(): Response
    {
        return $this->respondWithoutData(204);
    }
}

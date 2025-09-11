<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action\ServiceStatus;

use Psr\Http\Message\ResponseInterface as Response;
use Szabmik\Slim\Action\Action;

final class Liveness extends Action
{
    protected function action(): Response
    {
        return $this->respondWithoutData(204);
    }
}

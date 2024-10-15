<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Szabmik\Slim\Enum\ServerStatus;

class Health extends Action
{
    protected function action(): Response
    {
        return $this->respondWithData(
            [
                'status' => ServerStatus::Healthy->value,
                'timestamp' => (new DateTime())->format('c')
            ]
        );
    }
}

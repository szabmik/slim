<?php

declare(strict_types=1);

namespace Szabmik\Slim\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

abstract class Action
{
    protected Request $request;

    protected Response $response;

    protected array $args;

    public function __construct(protected ?LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    abstract protected function action(): Response;

    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new Payload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(Payload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }
}

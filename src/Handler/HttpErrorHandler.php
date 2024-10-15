<?php

declare(strict_types=1);

namespace Szabmik\Slim\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Szabmik\Slim\Action\Error\Error as ActionError;
use Szabmik\Slim\Action\Payload as ActionPayload;
use Szabmik\Slim\Enum\ActionErrorType;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ActionError(
            ActionErrorType::SERVER_ERROR->value,
            'An internal error has occurred while processing your request.'
        );

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $error->setDescription($exception->getMessage());

            if ($exception instanceof HttpNotFoundException) {
                $error->setType(ActionErrorType::RESOURCE_NOT_FOUND->value);
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $error->setType(ActionErrorType::NOT_ALLOWED->value);
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $error->setType(ActionErrorType::UNAUTHENTICATED->value);
            } elseif ($exception instanceof HttpForbiddenException) {
                $error->setType(ActionErrorType::INSUFFICIENT_PRIVILEGES->value);
            } elseif ($exception instanceof HttpBadRequestException) {
                $error->setType(ActionErrorType::BAD_REQUEST->value);
            } elseif ($exception instanceof HttpNotImplementedException) {
                $error->setType(ActionErrorType::NOT_IMPLEMENTED->value);
            }
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {
            $error->setDescription($exception->getMessage());
        }

        $payload = new ActionPayload($statusCode, null, $error);
        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}

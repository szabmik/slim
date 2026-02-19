<?php

declare(strict_types=1);

namespace Szabmik\Slim\Handler;

use Exception;
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
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\ProcessorInterface;

/**
 * Custom HTTP error handler for Slim applications.
 *
 * Translates various Slim-related HTTP exceptions into structured JSON error responses,
 * mapping them to standard ActionErrorType values. It ensures consistent and readable error
 * payloads across the application, suitable for API consumers.
 *
 * If detailed error display is enabled (e.g., in development), non-HTTP exceptions will
 * include the exception message in the response body.
 */
class HttpErrorHandler extends SlimErrorHandler
{
    /**
     * Generates a JSON response based on the captured exception.
     *
     * Matches known Slim HttpExceptions to corresponding ActionErrorType values.
     * For unknown exceptions, defaults to SERVER_ERROR unless displayErrorDetails is enabled.
     *
     * @throws Exception If JSON encoding fails.
     *
     * @return Response JSON response with error details and appropriate HTTP status code.
     */
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
            && $this->displayErrorDetails
        ) {
            $error->setDescription($exception->getMessage());
        }

        $uidProcessor = $this->getUidProcessor();
        if ($uidProcessor) {
            $error->setUid($uidProcessor->getUid());
        }

        $payload = new ActionPayload($statusCode, null, $error);
        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);
        if ($encodedPayload === false) {
            throw new Exception('Failed to encode json.');
        }
        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Returns the UID processor from the logger, if any.
     *
     * @return UidProcessor|null The UID processor or null if not found.
     */
    private function getUidProcessor(): ?UidProcessor
    {
        if (!$this->logger instanceof Logger) {
            return null;
        }

        $uidProcessor = array_filter($this->logger->getProcessors(), fn(ProcessorInterface $processor) => $processor instanceof UidProcessor);
        return $uidProcessor[0] ?? null;
    }
}

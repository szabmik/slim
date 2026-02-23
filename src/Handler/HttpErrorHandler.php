<?php

declare(strict_types=1);

namespace Szabmik\Slim\Handler;

use JsonException;
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
     * @throws JsonException If JSON encoding fails.
     *
     * @return Response JSON response with error details and appropriate HTTP status code.
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $errorType = ActionErrorType::SERVER_ERROR->value;
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();

            $errorType = match (true) {
                $exception instanceof HttpNotFoundException => ActionErrorType::RESOURCE_NOT_FOUND->value,
                $exception instanceof HttpMethodNotAllowedException => ActionErrorType::NOT_ALLOWED->value,
                $exception instanceof HttpUnauthorizedException => ActionErrorType::UNAUTHENTICATED->value,
                $exception instanceof HttpForbiddenException => ActionErrorType::INSUFFICIENT_PRIVILEGES->value,
                $exception instanceof HttpBadRequestException => ActionErrorType::BAD_REQUEST->value,
                $exception instanceof HttpNotImplementedException => ActionErrorType::NOT_IMPLEMENTED->value,
                default => ActionErrorType::SERVER_ERROR->value,
            };
        } elseif ($this->displayErrorDetails) {
            $description = $exception->getMessage();
        }

        $uid = $this->getUidProcessor()?->getUid();
        $error = new ActionError($errorType, $description, $uid);

        $payload = new ActionPayload($statusCode, null, $error);
        $encodedPayload = json_encode($payload, ActionPayload::JSON_FLAGS);
        
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
        return reset($uidProcessor) ?: null;
    }
}

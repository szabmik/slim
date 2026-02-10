<?php

declare(strict_types=1);

namespace Szabmik\Slim\Handler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

/**
 * Handles fatal PHP shutdown errors and converts them into structured HTTP responses.
 *
 * This class registers a shutdown function that captures the last error at script termination,
 * and—if applicable—routes it through the application's HTTP error handler. It ensures that
 * unexpected fatal errors are rendered consistently as JSON responses.
 *
 * Typically used as a fallback in production environments to avoid raw error output.
 */
class ShutdownHandler
{
    /**
     * Constructs a shutdown handler.
     *
     * @param Request $request The current request instance.
     * @param HttpErrorHandler $errorHandler A Slim-compatible error handler.
     * @param bool $displayErrorDetails Whether to include detailed error messages.
     */
    public function __construct(
        private Request $request,
        private HttpErrorHandler $errorHandler,
        private bool $displayErrorDetails
    ) {
    }

    /**
     * Executes when the PHP engine shuts down and captures the last fatal error if one occurred.
     *
     * Converts the error into an HttpInternalServerErrorException and delegates handling
     * to the HttpErrorHandler. Emits the resulting response immediately.
     */
    public function __invoke(): void
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $message = $this->getErrorMessage($error);
        $exception = new HttpInternalServerErrorException($this->request, $message);
        $response = $this->errorHandler->__invoke(
            $this->request,
            $exception,
            $this->displayErrorDetails,
            true,
            true,
        );

        // Use standard Slim emitter for shutdown errors (CORS not needed in error state)
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }

    /**
     * Builds a user-friendly error message based on the captured shutdown error array.
     *
     * The returned message varies depending on the type and the displayErrorDetails flag.
     *
     * @param array<string, mixed> $error The error array returned from error_get_last().
     *
     * @return string Formatted message for inclusion in the exception.
     */
    private function getErrorMessage(array $error): string
    {
        if (!$this->displayErrorDetails) {
            return 'An error while processing your request. Please try again later.';
        }

        $errorFile = $error['file'];
        $errorLine = $error['line'];
        $errorMessage = $error['message'];
        $errorType = $error['type'];

        if ($errorType === E_USER_ERROR) {
            return "FATAL ERROR: {$errorMessage}. on line {$errorLine} in file {$errorFile}.";
        }

        if ($errorType === E_USER_WARNING) {
            return "WARNING: {$errorMessage}";
        }

        if ($errorType === E_USER_NOTICE) {
            return "NOTICE: {$errorMessage}";
        }

        return "FATAL ERROR: {$errorMessage}. on line {$errorLine} in file {$errorFile}.";
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim;

use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Szabmik\Slim\Handler\HttpErrorHandler;
use Szabmik\Slim\Handler\ShutdownHandler;
use Szabmik\Slim\Middleware\RemoveTrailingSlash;
use Szabmik\Slim\Setting\AppSettings;

/**
 * Bridge class to initialize and configure a Slim App instance.
 *
 * This helper class provides a reusable method (`create()`) to bootstrap a Slim App,
 * register routes, middlewares, error handling, and system-wide shutdown behavior.
 * Designed to promote modularity and simplify application setup with optional
 * custom components (e.g. container, error handler).
 */
class Bridge
{
    /**
     * Creates and configures a Slim App instance with optional components.
     *
     * @param AppSettings $appSettings Application configuration (error visibility, logging, etc.)
     * @param callable $routes Callback to register application routes.
     * @param callable|null $middlewares Optional callback to register additional middlewares.
     * @param Container|null $container Optional DI container to be used by the app.
     * @param HttpErrorHandler|null $httpErrorHandler Optional custom error handler.
     * @param ShutdownHandler|null $shutdownHandler Optional custom shutdown handler.
     *
     * @return App The fully configured Slim App instance.
     */
    public static function create(
        AppSettings $appSettings,
        callable $routes,
        ?callable $middlewares = null,
        ?Container $container = null,
        ?HttpErrorHandler $httpErrorHandler = null,
        ?ShutdownHandler $shutdownHandler = null
    ): App {
        if (!is_null($container)) {
            AppFactory::setContainer($container);
        }

        $app = AppFactory::create();

        // Register routes
        call_user_func($routes, $app);

        // Register middlewares
        if (!is_null($middlewares)) {
            call_user_func($middlewares, $app);
        }

        // Create default error handler
        $httpErrorHandler = $httpErrorHandler ?? new HttpErrorHandler($app->getCallableResolver(), $app->getResponseFactory());

        // Create request object from globals
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        // Create default shutdown handler
        $shutdownHandler = $shutdownHandler ?? new ShutdownHandler($request, $httpErrorHandler, $appSettings->getDisplayErrorDetails());
        register_shutdown_function($shutdownHandler);

        // Add body parsing middleware
        $app->addBodyParsingMiddleware();

        // Add routing middleware
        $app->addRoutingMiddleware();

        // Add remove trailing slash middleware
        $app->add(new RemoveTrailingSlash());

        // Add content length middleware
        $app->add(new ContentLengthMiddleware());

        // Add error middleware
        $errorMiddleware = $app->addErrorMiddleware($appSettings->getDisplayErrorDetails(), $appSettings->getLogErrors(), $appSettings->getLogErrorDetails());
        $errorMiddleware->setDefaultErrorHandler($httpErrorHandler);

        return $app;
    }
}

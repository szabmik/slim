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

class Bridge
{
    public static function create(
        AppSettings $appSettings,
        callable $routes,
        callable $middlewares = null,
        ?Container $container = null,
        ?HttpErrorHandler $httpErrorHandler = null,
        ?ShutdownHandler $shutdownHandler = null
    ): App {
        if (!is_null($container)) {
            AppFactory::setContainer($container);
        }

        $app = AppFactory::create();

        // Register routes
        if (is_callable($routes)) {
            call_user_func($routes, $app);
        }

        // Register middlewares
        if (!is_null($middlewares) && is_callable($middlewares)) {
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

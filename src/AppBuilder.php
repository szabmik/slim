<?php

declare(strict_types=1);

namespace Szabmik\Slim;

use Closure;
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Middleware\ContentLengthMiddleware;
use Szabmik\Slim\Handler\HttpErrorHandler;
use Szabmik\Slim\Handler\ShutdownHandler;
use Szabmik\Slim\Middleware\RemoveTrailingSlash;
use Szabmik\Slim\ResponseEmitter\ResponseEmitter;
use Szabmik\Slim\Setting\AppSettings;

/**
 * Builder class for creating and configuring a Slim App instance.
 *
 * This class provides a fluent interface for step-by-step configuration
 * of a Slim application, following the Builder pattern to separate
 * construction from representation.
 *
 * Example usage:
 * ```php
 * $app = AppBuilder::create($settings)
 *     ->withContainer($container)
 *     ->withRoutes($routeCallback)
 *     ->withMiddlewares($middlewareCallback)
 *     ->build();
 * ```
 */
class AppBuilder
{
    private App $app;
    private AppSettings $settings;
    private ?Closure $routes = null;
    private ?Closure $middlewares = null;
    private ?HttpErrorHandler $httpErrorHandler = null;
    private ?ShutdownHandler $shutdownHandler = null;
    private ?ResponseEmitter $responseEmitter = null;

    /**
     * Private constructor to enforce factory method usage.
     *
     * @param AppSettings $settings Application settings.
     */
    private function __construct(AppSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Creates a new AppBuilder instance.
     *
     * @param AppSettings $settings Application configuration.
     *
     * @return self A new AppBuilder instance.
     */
    public static function create(AppSettings $settings): self
    {
        return new self($settings);
    }

    /**
     * Sets the DI container for the application.
     *
     * @param Container $container The PHP-DI container.
     *
     * @return self Returns self for method chaining.
     */
    public function withContainer(Container $container): self
    {
        AppFactory::setContainer($container);
        return $this;
    }

    /**
     * Sets the route registration callback.
     *
     * @param callable $routes Callback that receives the App instance and registers routes.
     *
     * @return self Returns self for method chaining.
     */
    public function withRoutes(callable $routes): self
    {
        $this->routes = $routes instanceof Closure ? $routes : Closure::fromCallable($routes);
        return $this;
    }

    /**
     * Sets the middleware registration callback.
     *
     * @param callable $middlewares Callback that receives the App instance and registers middlewares.
     *
     * @return self Returns self for method chaining.
     */
    public function withMiddlewares(callable $middlewares): self
    {
        $this->middlewares = $middlewares instanceof Closure ? $middlewares : Closure::fromCallable($middlewares);
        return $this;
    }

    /**
     * Sets a custom HTTP error handler.
     *
     * @param HttpErrorHandler $handler Custom error handler.
     *
     * @return self Returns self for method chaining.
     */
    public function withHttpErrorHandler(HttpErrorHandler $handler): self
    {
        $this->httpErrorHandler = $handler;
        return $this;
    }

    /**
     * Sets a custom shutdown handler.
     *
     * @param ShutdownHandler $handler Custom shutdown handler.
     *
     * @return self Returns self for method chaining.
     */
    public function withShutdownHandler(ShutdownHandler $handler): self
    {
        $this->shutdownHandler = $handler;
        return $this;
    }

    /**
     * Sets a custom response emitter with CORS support.
     *
     * If not set, the app will use ResponseEmitter::fromEnvironment() by default.
     *
     * @param ResponseEmitter $emitter Custom response emitter.
     *
     * @return self Returns self for method chaining.
     */
    public function withResponseEmitter(ResponseEmitter $emitter): self
    {
        $this->responseEmitter = $emitter;
        return $this;
    }

    /**
     * Builds and returns the fully configured Slim App instance.
     *
     * This method performs the following steps:
     * 1. Creates the Slim App
     * 2. Registers routes
     * 3. Registers custom middlewares
     * 4. Configures error handling
     * 5. Registers shutdown handler
     * 6. Adds default middlewares (body parsing, routing, etc.)
     *
     * Note: After build(), call run() to start the application with CORS support.
     *
     * @return App The fully configured Slim application.
     */
    public function build(): App
    {
        $this->app = AppFactory::create();

        $this->registerRoutes();
        $this->registerCustomMiddlewares();
        $this->configureErrorHandling();
        $this->registerShutdownFunction();
        $this->registerDefaultMiddlewares();

        return $this->app;
    }

    /**
     * Enables CORS support using environment-based origin whitelist.
     *
     * ⚠️ Only needed if your API is called from web browsers (SPA frontends)!
     * Not needed for: microservices, mobile apps, CLI tools, server-to-server.
     *
     * Reads ALLOWED_ORIGINS environment variable for origin whitelist.
     * Falls back to '*' (all origins) if not set.
     *
     * @return self Returns self for method chaining.
     */
    public function withCors(): self
    {
        $this->responseEmitter = ResponseEmitter::fromEnvironment();
        return $this;
    }

    /**
     * Builds and runs the Slim application.
     *
     * This is a convenience method that combines build() and run().
     *
     * By default, uses standard Slim ResponseEmitter (no CORS headers).
     * Use withCors() or withResponseEmitter() to enable CORS support.
     *
     * @return void
     */
    public function run(): void
    {
        $app = $this->build();

        // If custom emitter is set (e.g., via withCors() or withResponseEmitter()), use it
        // Otherwise, use standard Slim emitter (no CORS)
        if ($this->responseEmitter !== null) {
            $app->run($this->responseEmitter);
        } else {
            $app->run();  // Standard Slim emitter
        }
    }

    /**
     * Registers application routes using the provided callback.
     */
    private function registerRoutes(): void
    {
        if ($this->routes !== null) {
            ($this->routes)($this->app);
        }
    }

    /**
     * Registers custom middlewares using the provided callback.
     */
    private function registerCustomMiddlewares(): void
    {
        if ($this->middlewares !== null) {
            ($this->middlewares)($this->app);
        }
    }

    /**
     * Configures error handling middleware and sets the default error handler.
     */
    private function configureErrorHandling(): void
    {
        $this->httpErrorHandler = $this->httpErrorHandler ?? new HttpErrorHandler(
            $this->app->getCallableResolver(),
            $this->app->getResponseFactory()
        );

        $errorMiddleware = $this->app->addErrorMiddleware(
            $this->settings->getDisplayErrorDetails(),
            $this->settings->getLogErrors(),
            $this->settings->getLogErrorDetails()
        );

        $errorMiddleware->setDefaultErrorHandler($this->httpErrorHandler);
    }

    /**
     * Registers the shutdown handler function.
     */
    private function registerShutdownFunction(): void
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        $this->shutdownHandler = $this->shutdownHandler ?? new ShutdownHandler(
            $request,
            $this->httpErrorHandler,
            $this->settings->getDisplayErrorDetails()
        );

        register_shutdown_function($this->shutdownHandler);
    }

    /**
     * Registers default Slim middlewares in the correct order.
     *
     * Middleware order (from last to first in execution):
     * 1. Error Middleware (already added in configureErrorHandling)
     * 2. Content Length Middleware
     * 3. Remove Trailing Slash Middleware
     * 4. Routing Middleware
     * 5. Body Parsing Middleware
     */
    private function registerDefaultMiddlewares(): void
    {
        // Add body parsing middleware
        $this->app->addBodyParsingMiddleware();

        // Add routing middleware
        $this->app->addRoutingMiddleware();

        // Add remove trailing slash middleware
        $this->app->add(new RemoveTrailingSlash());

        // Add content length middleware
        $this->app->add(new ContentLengthMiddleware());
    }
}

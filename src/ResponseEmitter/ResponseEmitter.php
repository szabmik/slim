<?php

declare(strict_types=1);

namespace Szabmik\Slim\ResponseEmitter;

use Psr\Http\Message\ResponseInterface;
use Slim\ResponseEmitter as SlimResponseEmitter;

/**
 * Custom ResponseEmitter that extends Slim's emitter with enhanced CORS and caching headers.
 *
 * This class overrides the default `emit()` behavior of Slim's ResponseEmitter to inject
 * HTTP headers that support Cross-Origin Resource Sharing (CORS) and prevent caching.
 *
 * Key features:
 * - Dynamically sets `Access-Control-Allow-Origin` based on the request's origin header.
 * - Allows credentials and custom headers for secure API communication across domains.
 * - Prevents caching by setting strict `Cache-Control` and `Pragma` headers.
 * - Ensures output buffering doesn't interfere with response emission.
 *
 * Use this emitter if your Slim-based API is intended to be consumed from multiple
 * front-end origins (e.g. SPAs or mobile apps), especially during development or if
 * you require fine-grained control over CORS behavior.
 */
class ResponseEmitter extends SlimResponseEmitter
{
    /**
     * List of allowed origins for CORS requests.
     *
     * @var array<string>
     */
    private array $allowedOrigins;

    /**
     * Private constructor to enforce factory method usage.
     *
     * @param array<string> $allowedOrigins List of allowed origins.
     */
    private function __construct(array $allowedOrigins)
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    /**
     * Creates a ResponseEmitter from environment variable ALLOWED_ORIGINS.
     *
     * Expected format: comma-separated list (e.g., "https://app.example.com,https://admin.example.com")
     *
     * Environment variable sources (in order):
     * 1. getenv('ALLOWED_ORIGINS') - OS environment
     * 2. $_SERVER['ALLOWED_ORIGINS'] - Apache SetEnv
     * 3. Default: ['*'] - Development fallback (accepts all origins)
     *
     * @return self
     */
    public static function fromEnvironment(): self
    {
        $allowedOrigins = self::getOriginsFromEnvironment();
        return new self($allowedOrigins);
    }

    /**
     * Creates a ResponseEmitter with explicitly specified allowed origins.
     *
     * @param array<string> $allowedOrigins List of allowed origins (e.g., ['https://app.example.com', 'https://admin.example.com'])
     *
     * @return self
     */
    public static function withOrigins(array $allowedOrigins): self
    {
        return new self($allowedOrigins);
    }

    /**
     * Creates a ResponseEmitter that allows all origins (wildcard).
     *
     * ⚠️ WARNING: Use only in development! This disables CORS protection.
     *
     * @return self
     */
    public static function allowAllOrigins(): self
    {
        return new self(['*']);
    }

    /**
     * Retrieves allowed origins from the ALLOWED_ORIGINS environment variable.
     *
     * Expected format: comma-separated list (e.g., "https://app.example.com,https://admin.example.com")
     *
     * Uses getenv() for reliable environment variable access regardless of php.ini settings.
     *
     * @return array<string>
     */
    private static function getOriginsFromEnvironment(): array
    {
        // Use getenv() for reliable access (works regardless of variables_order in php.ini)
        $originsEnv = getenv('ALLOWED_ORIGINS');

        // Fallback to $_SERVER for Apache/nginx setups where SetEnv is used
        if ($originsEnv === false) {
            $originsEnv = $_SERVER['ALLOWED_ORIGINS'] ?? '';
        }

        if (empty($originsEnv)) {
            // Development fallback - accept all origins
            // In production, always set ALLOWED_ORIGINS environment variable
            return ['*'];
        }

        return array_map('trim', explode(',', $originsEnv));
    }

    /**
     * Validates and returns the allowed origin based on the request origin.
     *
     * @param string $requestOrigin The origin from the HTTP request.
     *
     * @return string The validated origin or empty string if not allowed.
     */
    private function getAllowedOrigin(string $requestOrigin): string
    {
        // If wildcard is allowed (development mode), return it
        if (in_array('*', $this->allowedOrigins, true)) {
            return '*';
        }

        // Check if the request origin is in the whitelist
        if (in_array($requestOrigin, $this->allowedOrigins, true)) {
            return $requestOrigin;
        }

        // Origin not allowed
        return '';
    }

    /**
     * Emits the HTTP response with additional CORS and cache-control headers.
     *
     * This method overrides the parent emitter to dynamically set
     * CORS (Cross-Origin Resource Sharing) headers based on the request origin,
     * allowing client applications from other origins to access the API.
     * It also disables client- and proxy-side caching to ensure fresh responses.
     *
     * Security: Only whitelisted origins are allowed. Configure via constructor
     * parameter or ALLOWED_ORIGINS environment variable.
     *
     * @param ResponseInterface $response The PSR-7 response to emit.
     */
    public function emit(ResponseInterface $response): void
    {
        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigin = $this->getAllowedOrigin($requestOrigin);

        // Only add CORS headers if origin is allowed
        if (!empty($allowedOrigin)) {
            $response = $response
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
                ->withHeader(
                    'Access-Control-Allow-Headers',
                    'X-Requested-With, Content-Type, Accept, Origin, Authorization',
                )
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
                ->withHeader('Pragma', 'no-cache');
        }

        if (ob_get_contents()) {
            ob_clean();
        }

        parent::emit($response);
    }
}

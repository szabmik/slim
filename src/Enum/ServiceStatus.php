<?php

declare(strict_types=1);

namespace Szabmik\Slim\Enum;

/**
 * Enumeration representing the general health status of a service.
 *
 * Used to standardize readiness or health check responses across the application.
 */
enum ServiceStatus: string
{
    /** The service is operational and functioning as expected. */
    case Healthy = 'healthy';
    /** The service is currently not functioning properly or unavailable. */
    case Unhealthy = 'unhealthy';
}

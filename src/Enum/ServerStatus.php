<?php

declare(strict_types=1);

namespace Szabmik\Slim\Enum;

enum ServerStatus: string
{
    case Healthy = 'healthy';
    case Unhealthy = 'unhealthy';
    case Degraded = 'degraded';
    case Maintenance = 'maintenance';
}

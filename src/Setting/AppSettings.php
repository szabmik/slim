<?php

declare(strict_types=1);

namespace Szabmik\Slim\Setting;

class AppSettings extends ArraySettings
{
    public function __construct(string $env, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails)
    {
        $this->settings['env'] = $env;
        $this->settings['displayErrorDetails'] = $displayErrorDetails;
        $this->settings['logErrors'] = $logErrors;
        $this->settings['logErrorDetails'] = $logErrorDetails;
    }

    public function getEnv(): string
    {
        return $this->settings['env'];
    }

    public function getDisplayErrorDetails(): bool
    {
        return $this->settings['displayErrorDetails'];
    }

    public function getLogErrors(): bool
    {
        return $this->settings['logErrors'];
    }

    public function getLogErrorDetails(): bool
    {
        return $this->settings['logErrorDetails'];
    }
}

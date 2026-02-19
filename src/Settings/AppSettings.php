<?php

declare(strict_types=1);

namespace Szabmik\Slim\Settings;

/**
 * Concrete settings implementation for application configuration.
 *
 * Stores specific application-level configuration values such as the app name,
 * environment, and error reporting flags. These settings are used throughout
 * the Slim app to configure behavior like error visibility and logging.
 */
class AppSettings extends ArraySettings
{
    /**
     * Initializes the application settings.
     *
     * @param string $name Application name.
     * @param string $env Environment type (e.g., "production", "development").
     * @param bool $displayErrorDetails Whether to display detailed error messages.
     * @param bool $logErrors Whether to log errors.
     * @param bool $logErrorDetails Whether to log detailed error information.
     */
    public function __construct(string $name, string $env, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails)
    {
        $this->settings['name'] = $name;
        $this->settings['env'] = $env;
        $this->settings['displayErrorDetails'] = $displayErrorDetails;
        $this->settings['logErrors'] = $logErrors;
        $this->settings['logErrorDetails'] = $logErrorDetails;
    }

    /**
     * Returns the application name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->settings['name'];
    }

    /**
     * Returns the current environment name (e.g., dev, prod).
     *
     * @return string
     */
    public function getEnv(): string
    {
        return $this->settings['env'];
    }

    /**
     * Indicates whether error details should be displayed.
     *
     * @return bool
     */
    public function getDisplayErrorDetails(): bool
    {
        return $this->settings['displayErrorDetails'];
    }

    /**
     * Indicates whether errors should be logged.
     *
     * @return bool
     */
    public function getLogErrors(): bool
    {
        return $this->settings['logErrors'];
    }

    /**
     * Indicates whether detailed error information should be logged.
     *
     * @return bool
     */
    public function getLogErrorDetails(): bool
    {
        return $this->settings['logErrorDetails'];
    }
}

<?php

declare(strict_types=1);

namespace Szabmik\Slim\Settings;

/**
 * Settings class for configuring JSON schema validation behavior.
 *
 * Stores the file system path where JSON schemas are located and an optional
 * prefix for distinguishing or grouping schema references.
 * Useful when working with middleware or services that validate request or response
 * payloads using standardized JSON Schema definitions.
 */
class JSONSchemaValidatorSettings extends ArraySettings
{
    /**
     * Initializes schema validation settings.
     *
     * @param string $schemaFolder Path to the folder containing JSON Schema files.
     * @param string|null $prefix Optional prefix used for schema resolution or namespacing.
     */
    public function __construct(private string $schemaFolder, private ?string $prefix = null)
    {
        $this->settings['schemaFolder'] = $schemaFolder;
        $this->settings['prefix'] = $prefix;
    }

    /**
     * Returns the path to the folder where schemas are stored.
     *
     * @return string
     */
    public function getSchemaFolder(): string
    {
        return $this->settings['schemaFolder'];
    }

    /**
     * Returns the optional prefix for schema referencing or namespacing.
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->settings['prefix'];
    }
}

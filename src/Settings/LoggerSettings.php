<?php

declare(strict_types=1);

namespace Szabmik\Slim\Settings;

use Monolog\Level;

class LoggerSettings extends ArraySettings
{
    public function __construct(string $name, string $stream, Level $level)
    {
        $this->settings['name'] = $name;
        $this->settings['stream'] = $stream;
        $this->settings['level'] = $level;
    }

    public function getName()
    {
        return $this->settings['name'];
    }

    public function getStream()
    {
        return $this->settings['stream'];
    }

    public function getLevel()
    {
        return $this->settings['level'];
    }
}

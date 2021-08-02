<?php


namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

class UserNamedLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('UserNamedLogger');

        // Configure Monolog to log on user named log files
        $filename = storage_path('logs/laravel-'.  posix_getpwuid(posix_geteuid())['name'] .'.log');
        $rotatingHandler = new RotatingFileHandler($filename);
        $logger->pushHandler($rotatingHandler);

        return $logger;
    }
}

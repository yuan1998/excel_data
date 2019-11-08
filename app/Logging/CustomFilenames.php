<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;

class CustomFilenames
{
    /**
     * Customize the given logger instance.
     *
     * @param \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof RotatingFileHandler) {
                $fix = function_exists('posix_getpwuid')
                && function_exists('posix_geteuid')
                    ? php_sapi_name()
                    . '-' . posix_getpwuid(posix_geteuid())['name']
                    . '-' . get_current_user()
                    : '';
                $handler->setFilenameFormat("{filename}-$fix-{date}", 'Y-m-d');
            }
        }
    }
}

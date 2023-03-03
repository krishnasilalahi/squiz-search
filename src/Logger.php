<?php

namespace Squiz\PhpCodeExam;

class Logger
{
    /**
     * @param $message
     * @param string $type
     */
    function logMsg($message, string $type = 'error')
    {
        if (!is_dir(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs');
        }

        $file = match ($type) {
            'error' => __DIR__ . '/../logs/error.log',
            'request' => __DIR__ . '/../logs/request.log',
            'response' => __DIR__ . '/../logs/response.log',
            default => __DIR__ . '/../logs/log.log'
        };
        error_log($message.PHP_EOL, 3, $file);
    }
}

<?php

namespace LaraTui;

class Logger
{
    private static State $state;

    public static function init(State $state)
    {
        self::$state = $state;
    }

    public static function log(string $message): void
    {
        $time = date('d-m-Y h:i:s');
        $message = "[$time] $message" . PHP_EOL;
        file_put_contents('application.log', $message, FILE_APPEND);
        self::$state->append('_log', $message);
    }
}

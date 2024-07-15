<?php

namespace LaraTui\Commands;

use LaraTui\SystemExec;

class TailLogsCommand extends Command
{
    public function __invoke(SystemExec $systemExec): void
    {
        $systemExec(['tail', '-f', '-n', '100', './storage/logs/laravel.log'], 'app_log');
    }
}

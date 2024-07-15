<?php

namespace LaraTui\Commands;

use LaraTui\SystemExec;

class RunArtisanCommand extends Command
{
    public function __construct(private readonly string $command) {}

    public function __invoke(SystemExec $systemExec): void
    {
        $systemExec(['php', 'artisan', $this->command], 'artisan_command');
    }
}

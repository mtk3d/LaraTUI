<?php

namespace LaraTui\Commands;

use LaraTui\State;
use LaraTui\SystemExec;

class UpdatePackagesCommand extends Command
{
    public function __invoke(SystemExec $systemExec, State $state): void
    {
        $systemExec(['composer', 'update', '--no-progress', '--no-interaction', '--no-ansi', '--no-scripts'], 'update_log')
            ->then(fn () => $systemExec(['php', 'artisan', 'package:discover', '--no-ansi'], 'update_log'))
            ->then(fn () => $state->append('update_log', 'Finished! Press ESC to close'.PHP_EOL));
    }
}

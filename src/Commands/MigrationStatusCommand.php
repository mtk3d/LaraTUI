<?php

namespace LaraTui\Commands;

use LaraTui\State;
use LaraTui\State\MigrationStatus;
use LaraTui\SystemExec;

class MigrationStatusCommand extends Command
{
    public function __invoke(State $state, SystemExec $systemExec): void
    {
        $systemExec(['./vendor/bin/sail', 'artisan', 'migrate:status'])
            ->then(function ($output) use ($state) {
                $state->set(MigrationStatus::class, MigrationStatus::fromMigrationStatusCommand($output));
            });
    }
}

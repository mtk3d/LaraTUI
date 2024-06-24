<?php

namespace LaraTui\Commands;

use LaraTui\State\MigrationStatus;

class MigrationStatusCommand extends Command
{
    public function execute(array $data): void
    {
        $this->execCommand('./vendor/bin/sail artisan migrate:status')
            ->then(function ($output) {
                $this->state->set(MigrationStatus::class, MigrationStatus::fromMigrationStatusCommand($output));
            });
    }
}

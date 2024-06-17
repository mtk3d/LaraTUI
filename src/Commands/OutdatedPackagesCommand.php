<?php

namespace LaravelSailTui\Commands;

use React\ChildProcess\Process;

class OutdatedPackagesCommand extends Command
{
    public static string $commandName = 'laravel_packages_command';

    public function init(): void
    {
    }

    public function execute(array $data): void
    {
        $process = new Process('composer outdated --format=json');

        $process->start($this->loop);

        $process->stdout->on('data', function ($chunk) {
            $this->state->append('outdated_packages_stream', $chunk);
        });

        $process->stdout->on('end', function () {
            $packagesInfo = json_decode($this->state->get('outdated_packages_stream'), true);

            if (isset($packagesInfo['installed'])) {
                $this->state->set('outdated_packages', $packagesInfo['installed']);
            }
        });
    }
}

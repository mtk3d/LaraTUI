<?php

namespace LaravelSailTui\Commands;

use React\ChildProcess\Process;

class ServicesStatusCommand extends Command
{
    public static string $commandName = 'services_status_command';

    public function init(): void
    {
        $this->state->set('sail_services_status', 'Loading...');
    }

    public function execute(array $data): void
    {
        $process = new Process('vendor/bin/sail ps -a --format=json | jq -s');

        $process->start($this->loop);

        $process->stdout->on('data', function ($chunk) {
            $this->state->append('services_status_stream', $chunk);
        });

        $process->stdout->on('end', function () {
            $fixedJson = $this->state->get('services_status_stream');
            $this->state->delete('services_status_stream');
            $servicesStatus = json_decode($fixedJson, true);

            $result = [];

            foreach ($servicesStatus as $service) {
                $result[$service['Service']] = $service['State'];
            }

            $this->state->set('services_status', $result);
        });
    }
}

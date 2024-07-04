<?php

namespace LaraTui\Commands;

use LaraTui\State;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class ServicesStatusCommand extends Command
{
    public function __invoke(State $state, LoopInterface $loop): void
    {
        $state->set('sail_services_status', 'Loading...');

        $process = new Process('vendor/bin/sail ps -a --format=json | jq -s');

        $process->start($loop);

        $process->stdout->on('data', function ($chunk) use ($state) {
            $state->append('services_status_stream', $chunk);
        });

        $process->stdout->on('end', function () use ($state) {
            $fixedJson = $state->get('services_status_stream');
            $state->delete('services_status_stream');
            $servicesStatus = json_decode($fixedJson, true);

            $result = [];

            foreach ($servicesStatus as $service) {
                $result[$service['Service']] = $service['State'];
            }

            $state->set('services_status', $result);
        });
    }
}

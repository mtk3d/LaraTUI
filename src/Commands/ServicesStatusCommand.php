<?php

namespace LaraTui\Commands;

use LaraTui\State;
use LaraTui\SystemExec;

class ServicesStatusCommand extends Command
{
    public function __invoke(State $state, SystemExec $systemExec): void
    {
        $systemExec(['sh', '-c', 'vendor/bin/sail ps -a --format=json | jq -s'])
            ->then(function (string $output) use ($state) {
                $servicesStatus = json_decode($output, true);
                $statuses = array_column($servicesStatus, 'State', 'Service');

                $state->set('services_status', $statuses);
            });
    }
}

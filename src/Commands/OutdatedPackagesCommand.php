<?php

namespace LaraTui\Commands;

use LaraTui\State;
use LaraTui\State\OutdatedPackages;
use LaraTui\SystemExec;

class OutdatedPackagesCommand extends Command
{
    public function init(): void {}

    public function __invoke(State $state, SystemExec $systemExec): void
    {
        $systemExec('composer', 'outdated', '--direct', '--format=json')
            ->then(function ($output) use ($state) {
                $state->set(
                    OutdatedPackages::class,
                    OutdatedPackages::fromJson($output),
                );
            });
    }
}

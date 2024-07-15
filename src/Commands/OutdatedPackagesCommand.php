<?php

namespace LaraTui\Commands;

use LaraTui\State;
use LaraTui\State\OutdatedPackages;
use LaraTui\SystemExec;
use React\Promise\PromiseInterface;

class OutdatedPackagesCommand extends Command
{
    public function init(): void {}

    public function __invoke(State $state, SystemExec $systemExec): PromiseInterface
    {
        return $systemExec(['composer', 'outdated', '--direct', '--format=json'])
            ->then(function ($output) use ($state) {
                $state->set(
                    OutdatedPackages::class,
                    OutdatedPackages::fromJson($output),
                );
            });
    }
}

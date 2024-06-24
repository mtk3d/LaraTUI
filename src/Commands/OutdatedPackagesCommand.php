<?php

namespace LaraTui\Commands;

use LaraTui\State\OutdatedPackages;

class OutdatedPackagesCommand extends Command
{
    public function init(): void {}

    public function execute(array $data): void
    {
        $this->execCommand('composer outdated --direct --format=json')
            ->then(function ($output) {
                $this->state->set(
                    OutdatedPackages::class,
                    OutdatedPackages::fromJson($output),
                );
            });
    }
}

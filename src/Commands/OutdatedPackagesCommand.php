<?php

namespace LaraTui\Commands;

class OutdatedPackagesCommand extends Command
{
    public function init(): void {}

    public function execute(array $data): void
    {
        $this->execCommand('composer outdated --format=json')
            ->then(function ($output) {
                $packagesInfo = json_decode($output, true);

                if (isset($packagesInfo['installed'])) {
                    $this->state->set('outdated_packages', $packagesInfo['installed']);
                }
            });
    }
}

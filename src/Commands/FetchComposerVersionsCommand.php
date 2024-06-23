<?php

namespace LaraTui\Commands;

use LaraTui\State\InstalledPackages;

class FetchComposerVersionsCommand extends Command
{
    public function execute(array $data): void
    {
        $this->execCommand('composer show --direct --format=json')
            ->then(function ($output) {
                $this->state->set(
                    InstalledPackages::class,
                    InstalledPackages::fromJson($output),
                );
            });
    }
}

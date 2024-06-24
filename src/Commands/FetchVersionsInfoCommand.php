<?php

namespace LaraTui\Commands;

use LaraTui\State\ComposerVersion;
use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersions;
use LaraTui\State\OutdatedPackages;
use LaraTui\State\PHPVersions;
use React\Http\Message\Response;

use function React\Promise\all;

class FetchVersionsInfoCommand extends Command
{
    const string LARAVEL_VERSIONS_URL = 'https://laravelversions.com/api/versions';

    const string PHP_VERSIONS_URL = 'https://php.watch/api/v1/versions';

    public function execute(array $data): void
    {
        all([
            $this->browser
                ->get(self::LARAVEL_VERSIONS_URL)
                ->then(function (Response $response) {
                    $body = $response->getBody()->getContents();
                    $this->state->set(
                        LaravelVersions::class,
                        LaravelVersions::fromResponseBody($body),
                    );
                }),
            $this->execCommand('composer show --direct --format=json')
                ->then(function ($output) {
                    $this->state->set(
                        InstalledPackages::class,
                        InstalledPackages::fromJson($output),
                    );
                }),
            $this->browser->get(self::PHP_VERSIONS_URL)
                ->then(function (Response $response) {
                    $body = $response->getBody()->getContents();
                    $this->state->set(PHPVersions::class, PHPVersions::fromResponseBody($body));
                }),
            $this->execCommand('composer outdated --direct --format=json')
                ->then(function ($output) {
                    $this->state->set(
                        OutdatedPackages::class,
                        OutdatedPackages::fromJson($output),
                    );
                }),
            $this->execCommand('composer --version')
                ->then(function ($output) {
                    $this->state->set(
                        ComposerVersion::class,
                        ComposerVersion::fromComposerVersionCommand($output),
                    );
                }),
        ])->then(function () {
            $this->eventBus->emit('PHPVersionsFetched');
            $this->commandBus->dispatch(BuildVersionsInfo::class);
        });
    }
}

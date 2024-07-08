<?php

namespace LaraTui\Commands;

use LaraTui\CommandInvoker;
use LaraTui\EventBus;
use LaraTui\State;
use LaraTui\State\ComposerVersion;
use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersions;
use LaraTui\State\OutdatedPackages;
use LaraTui\State\PHPVersions;
use LaraTui\SystemExec;
use React\Http\Browser;
use React\Http\Message\Response;

use function React\Promise\all;

class FetchVersionsInfoCommand extends Command
{
    const string LARAVEL_VERSIONS_URL = 'https://laravelversions.com/api/versions';

    const string PHP_VERSIONS_URL = 'https://php.watch/api/v1/versions';

    public function __invoke(State $state, Browser $browser, EventBus $eventBus, CommandInvoker $commandInvoker, SystemExec $systemExec): void
    {
        all([
            $browser
                ->get(self::LARAVEL_VERSIONS_URL)
                ->then(function (Response $response) use ($state) {
                    $body = $response->getBody()->getContents();
                    $state->set(
                        LaravelVersions::class,
                        LaravelVersions::fromResponseBody($body),
                    );
                }),
            $systemExec(['composer', 'show', '--direct', '--format=json'])
                ->then(function ($output) use ($state) {
                    $state->set(
                        InstalledPackages::class,
                        InstalledPackages::fromJson($output),
                    );
                }),
            $browser->get(self::PHP_VERSIONS_URL)
                ->then(function (Response $response) use ($state) {
                    $body = $response->getBody()->getContents();
                    $state->set(PHPVersions::class, PHPVersions::fromResponseBody($body));
                }),
            $systemExec(['composer', 'outdated', '--direct', '--format=json'])
                ->then(function ($output) use ($state) {
                    $state->set(
                        OutdatedPackages::class,
                        OutdatedPackages::fromJson($output),
                    );
                }),
            $systemExec(['composer', '--version'])
                ->then(function ($output) use ($state) {
                    $state->set(
                        ComposerVersion::class,
                        ComposerVersion::fromComposerVersionCommand($output),
                    );
                }),
        ])->then(function () use ($eventBus, $commandInvoker) {
            $eventBus->emit('PHPVersionsFetched');
            $commandInvoker->invoke(new BuildVersionsInfo());
        });
    }
}

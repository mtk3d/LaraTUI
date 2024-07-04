<?php

namespace LaraTui\Commands;

use Illuminate\Support\Str;
use LaraTui\EventBus;
use LaraTui\State;
use LaraTui\State\ComposerVersion;
use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersions;
use LaraTui\State\PHPVersions;
use LaraTui\State\VersionInfoLine;
use LaraTui\State\VersionsInfo;

class BuildVersionsInfo extends Command
{
    public function __invoke(State $state, EventBus $eventBus): void
    {
        $composerVersion = $state->get(ComposerVersion::class);
        $installedPackages = $state->get(InstalledPackages::class);
        $laravelVersions = $state->get(LaravelVersions::class);
        $phpVersions = $state->get(PHPVersions::class);

        $laravelPackageInfo = collect($installedPackages->installed)
            ->first(fn ($package) => $package->name === 'laravel/framework');

        $laravelMajor = Str::match('/^v(\d+)\./', $laravelPackageInfo->version);

        $laravelVersionInfo = collect($laravelVersions->data)
            ->first(fn ($package) => $package->major == $laravelMajor);

        $isSupported = $laravelVersionInfo->status === 'active';
        $isLatest = $laravelVersionInfo->latest === Str::after($laravelPackageInfo->version, 'v');

        $laravelInfo = new VersionInfoLine(
            'Laravel',
            $laravelPackageInfo->version,
            $isLatest,
            $isSupported,
            '(update info)',
        );

        $phpMajorMinor = Str::beforeLast($composerVersion->phpVersion, '.');
        $phpInfo = collect($phpVersions->data)
            ->first(fn ($php) => $php->name === $phpMajorMinor);

        $phpVersion = new VersionInfoLine(
            'PHP',
            $composerVersion->phpVersion,
            $phpInfo->isLatestVersion,
            ! $phpInfo->isEOLVersion,
            '(update info)',
        );

        $composerVersion = new VersionInfoLine(
            'Composer',
            $composerVersion->composerVersion,
            true,
            true,
            '(update info)',
        );

        $state->set(VersionsInfo::class, new VersionsInfo([
            $laravelInfo,
            $phpVersion,
            $composerVersion,
        ]));

        $eventBus->emit('BuildVersionsFinished');
    }
}

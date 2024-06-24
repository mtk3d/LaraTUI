<?php

namespace LaraTui\Commands;

use Illuminate\Support\Str;
use LaraTui\State\ComposerVersion;
use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersions;
use LaraTui\State\PHPVersions;
use LaraTui\State\VersionInfoLine;
use LaraTui\State\VersionsInfo;

class BuildVersionsInfo extends Command
{
    public function execute(array $data): void
    {
        $composerVersion = $this->state->get(ComposerVersion::class);
        $installedPackages = $this->state->get(InstalledPackages::class);
        $laravelVersions = $this->state->get(LaravelVersions::class);
        $phpVersions = $this->state->get(PHPVersions::class);

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

        $this->state->set(VersionsInfo::class, new VersionsInfo([
            $laravelInfo,
            $phpVersion,
            $composerVersion,
        ]));

        $this->eventBus->emit('BuildVersionsFinished');
    }
}

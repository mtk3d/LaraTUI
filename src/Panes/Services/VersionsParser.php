<?php

namespace LaraTui\Panes\Services;

use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersion;
use LaraTui\State\LaravelVersions;
use LaraTui\State\PHPVersions;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;

class VersionsParser
{
    public static function parseVersions(
        string $existingPhp,
        InstalledPackages $installedPackages,
        LaravelVersions $laravelVersions,
        PHPVersions $phpVersions,
    ) {
        $laravelVersionsInfo = array_filter($installedPackages->installed, function ($package) {
            return $package->name === 'laravel/framework';
        });

        $laravelInfo = [];

        if (! empty($laravelVersionsInfo)) {
            $laravelInfo = array_values($laravelVersionsInfo)[0];
        }

        $installedLaravelVersions = $laravelInfo->version;
        $laravelMajor = null;
        if (preg_match('/^v(\d+)\./', $installedLaravelVersions, $matches)) {
            $laravelMajor = $matches[1];
        }

        dump($laravelVersions);
        $laravelVersion = array_filter($laravelVersions->data ?? [], function ($version) use ($laravelMajor) {
            return $version->major == $laravelMajor;
        });

        $laravelVersionData = [];
        if (! empty($laravelVersion)) {
            /** @var LaravelVersion $laravelVersionData * */
            $laravelVersionData = array_values($laravelVersion)[0];
        }

        $supported = $laravelVersionData->status ?? '' === 'active';

        $color = Style::default();
        if ($supported) {
            if ('v'.$laravelVersionData->latest === $installedLaravelVersions) {
                $color->green();
            } else {
                $color->yellow();
            }
        } else {
            $color->red();
        }

        $versionSpan = Span::fromString(" $installedLaravelVersions ")->patchStyle($color);
        $supportedSpan = Span::fromString($supported ? 'Supported' : 'Outdated')->patchStyle($color);

        $latestSpan = Span::fromString(' Latest: '.$laravelVersionData->latest);

        return Line::fromSpans($versionSpan, $supportedSpan, $latestSpan)->patchStyle($color);
    }
}

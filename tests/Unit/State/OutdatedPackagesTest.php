<?php

use LaraTui\State\OutdatedPackages;

describe('test outdated packages state', function () {
    test('can be created from json', function () {
        $json = '
           {
                "installed": [
                    {
                        "name": "laravel/framework",
                        "direct-dependency": true,
                        "homepage": "https://laravel.com",
                        "source": "https://github.com/laravel/framework",
                        "version": "v11.10.0",
                        "latest": "v11.11.1",
                        "latest-status": "semver-safe-update",
                        "description": "The Laravel Framework.",
                        "abandoned": false
                    },
                    {
                        "name": "laravel/pint",
                        "direct-dependency": true,
                        "homepage": "https://laravel.com",
                        "source": "https://github.com/laravel/pint",
                        "version": "v1.16.0",
                        "latest": "v1.16.1",
                        "latest-status": "semver-safe-update",
                        "description": "An opinionated code formatter for PHP.",
                        "abandoned": false
                    }
                ]
            }
        ';

        $outdatedPacakges = OutdatedPackages::fromJson($json);

        expect($outdatedPacakges->installed[0]->latest)->toBe('v11.11.1');
        expect($outdatedPacakges->installed[0]->directDependency)->toBe(true);
        expect($outdatedPacakges->installed[1]->name)->toBe('laravel/pint');
    });
});

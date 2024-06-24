<?php

use LaraTui\State\InstalledPackages;

describe('test mapping installed packages state', function () {
    test('can parse json kebab case', function () {
        $json = '
            {
                "installed": [
                    {
                        "name": "pestphp/pest",
                        "direct-dependency": true,
                        "homepage": null,
                        "source": "https://github.com/pestphp/pest/tree/v2.34.8",
                        "version": "v2.34.8",
                        "description": "The elegant PHP Testing Framework.",
                        "abandoned": false
                    },
                    {
                        "name": "pestphp/pest-plugin-laravel",
                        "direct-dependency": true,
                        "homepage": null,
                        "source": "https://github.com/pestphp/pest-plugin-laravel/tree/v2.4.0",
                        "version": "v2.4.0",
                        "description": "The Pest Laravel Plugin",
                        "abandoned": false
                    }
                ]
            }';

        $packages = InstalledPackages::fromJson($json);

        expect($packages->installed[0]->directDependency)->toBe(true);
    });
});

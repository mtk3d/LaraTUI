<?php

use LaraTui\State\ComposerVersion;

describe('test creating php and composer versions state', function () {
    test('can be created from command output', function () {
        $commandOutput = <<<'SHELL'
            Composer version 2.7.6 2024-05-04 23:03:15
            PHP version 8.3.8 (/opt/homebrew/Cellar/php/8.3.8/bin/php)
            Run the "diagnose" command to get more detailed diagnostics output.
            SHELL;

        $composerVersion = ComposerVersion::fromComposerVersionCommand($commandOutput);

        expect($composerVersion->composerVersion)->toBe('2.7.6');
        expect($composerVersion->phpVersion)->toBe('8.3.8');
    });

    test('can handle wrong command output', function () {
        $commandOutput = <<<'SHELL'
            Command "ddd" is not defined.
            SHELL;

        $composerVersion = ComposerVersion::fromComposerVersionCommand($commandOutput);

        expect($composerVersion->composerVersion)->toBe('Unknown');
        expect($composerVersion->phpVersion)->toBe('Unknown');
    });
});

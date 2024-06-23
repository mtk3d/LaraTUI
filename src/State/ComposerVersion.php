<?php

namespace LaraTui\State;

class ComposerVersion
{
    public function __construct(
        public readonly string $composerVersion,
        public readonly string $phpVersion,
    ) {}

    public static function fromComposerVersionCommand(string $composerOutput): self
    {
        preg_match('/Composer version (\S+)/', $composerOutput, $composerMatches);
        $composerVersion = $composerMatches[1] ?? 'Unknown';

        preg_match('/PHP version (\S+)/', $composerOutput, $phpMatches);
        $composerPhpVersion = $phpMatches[1] ?? 'Unknown';

        return new self($composerVersion, $composerPhpVersion);
    }
}

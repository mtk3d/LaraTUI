<?php

namespace LaraTui\State;

class VersionInfoLine
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly bool $isLatest,
        public readonly bool $isSupported,
        public readonly string $updateInfo,
    ) {}
}

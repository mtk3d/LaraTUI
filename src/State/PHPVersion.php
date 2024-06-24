<?php

namespace LaraTui\State;

class PHPVersion
{
    public function __construct(
        public int $versionId,
        public string $name,
        public string $releaseDate,
        public string $activeSupportEndDate,
        public string $eolDate,
        public bool $isEOLVersion,
        public bool $isSecureVersion,
        public bool $isLatestVersion,
        public bool $isFutureVersion,
        public string $statusLabel,
    ) {}
}

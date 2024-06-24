<?php

namespace LaraTui\State;

class PHPVersion
{
    public function __construct(
        public int $versionId,
        public string $name,
        public string|null $releaseDate,
        public string|null $activeSupportEndDate,
        public string|null $eolDate,
        public bool $isEOLVersion,
        public bool $isSecureVersion,
        public bool $isLatestVersion,
        public bool $isFutureVersion,
        public string $statusLabel,
    ) {}
}

<?php

namespace LaraTui\State;

class LaravelVersion
{
    /** @param string[]|null $supportedPhp */
    public function __construct(
        public int $major,
        public int|null $latestMinor,
        public int $latestPatch,
        public string $latest,
        public string $releasedAt,
        public string|null $endsBugfixesAt,
        public string|null $endsSecurityfixesAt,
        public array $supportedPhp,
        public string $status,
    ) {}
}

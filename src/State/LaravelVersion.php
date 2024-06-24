<?php

namespace LaraTui\State;

class LaravelVersion
{
    /** @param string[]|null $supportedPhp */
    public function __construct(
        public int $major,
        public ?int $latestMinor,
        public ?int $latestPatch,
        public string $latest,
        public ?string $releasedAt,
        public ?string $endsBugfixesAt,
        public ?string $endsSecurityfixesAt,
        public ?array $supportedPhp,
        public string $status,
    ) {}
}

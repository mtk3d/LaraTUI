<?php

namespace LaraTui\State;

class Package
{
    public function __construct(
        public string $name,
        public ?bool $directDependency,
        public ?string $homepage,
        public string $source,
        public string $version,
        public string $description,
        public bool $abandoned,
        public ?string $latest,
        public ?string $latestStatus,
    ) {}
}

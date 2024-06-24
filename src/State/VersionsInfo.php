<?php

namespace LaraTui\State;

class VersionsInfo
{
    /** @param VersionInfoLine[] $lines */
    public function __construct(
        public readonly array $lines,
    ) {}
}

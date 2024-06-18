<?php

namespace LaraTui\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Periodic
{
    public function __construct(
        public readonly float $interval,
    )
    {}
}


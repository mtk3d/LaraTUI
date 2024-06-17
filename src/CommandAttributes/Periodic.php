<?php

namespace LaravelSailTui\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Periodic
{
    public function __construct(
        public readonly float $interval,
    )
    {}
}


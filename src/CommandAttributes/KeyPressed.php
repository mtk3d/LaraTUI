<?php

namespace LaraTui\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class KeyPressed
{
    public function __construct(
        public readonly string $key,
        public readonly bool $global = false,
    )
    {}
}


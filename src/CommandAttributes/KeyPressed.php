<?php

namespace LaraTui\CommandAttributes;

use Attribute;
use PhpTui\Term\KeyCode;

#[Attribute(Attribute::TARGET_METHOD)]
class KeyPressed
{
    public function __construct(
        public readonly KeyCode|string $key,
        public readonly bool $global = false,
    ) {}
}

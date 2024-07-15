<?php

namespace LaraTui\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Mouse
{
    public $key = 'Mouse';

    public function __construct(
        public readonly bool $global = false,
    ) {}
}

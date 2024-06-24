<?php

namespace LaraTui\Mapper;

use Brick\JsonMapper\NameMapper;
use Illuminate\Support\Str;

class CamelCaseToKebabCaseMapper implements NameMapper
{
    public function mapName(string $name): string
    {
        return Str::kebab($name);
    }
}

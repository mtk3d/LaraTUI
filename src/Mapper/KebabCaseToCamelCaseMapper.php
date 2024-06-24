<?php

namespace LaraTui\Mapper;

use Brick\JsonMapper\NameMapper;
use Illuminate\Support\Str;

class KebabCaseToCamelCaseMapper implements NameMapper
{
    public function mapName(string $name): string
    {
        return Str::camel($name);
    }
}

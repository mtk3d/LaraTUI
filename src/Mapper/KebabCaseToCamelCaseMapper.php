<?php

namespace LaraTui\Mapper;

use Brick\JsonMapper\NameMapper;

class KebabCaseToCamelCaseMapper implements NameMapper
{
    public function mapName(string $name): string
    {
        return preg_replace_callback(
            '/-([a-z])/',
            fn (array $matches) => strtoupper($matches[1]),
            $name,
        );
    }
}

<?php

namespace LaraTui\Mapper;

use Brick\JsonMapper\NameMapper;

class CamelCaseToKebabCaseMapper implements NameMapper
{
    public function mapName(string $name): string
    {
        return preg_replace_callback(
            '/[A-Z]/',
            fn (array $matches) => '-'.strtolower($matches[0]),
            $name,
        );
    }
}

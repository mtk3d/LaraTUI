<?php

namespace LaraTui\State;

use Brick\JsonMapper\JsonMapper;
use Brick\JsonMapper\NameMapper\CamelCaseToSnakeCaseMapper;
use Brick\JsonMapper\NameMapper\SnakeCaseToCamelCaseMapper;
use Brick\JsonMapper\OnExtraProperties;
use Brick\JsonMapper\OnMissingProperties;

class LaravelVersions
{
    /** @param LaravelVersion[] $data */
    public function __construct(
        public array $data,
    ) {}

    public static function fromResponseBody(string $data): self
    {
        return (new JsonMapper(
            onMissingProperties: OnMissingProperties::SET_NULL,
            onExtraProperties: OnExtraProperties::IGNORE,
            jsonToPhpNameMapper: new SnakeCaseToCamelCaseMapper(),
            phpToJsonNameMapper: new CamelCaseToSnakeCaseMapper(),
        ))
            ->map($data, self::class);
    }
}

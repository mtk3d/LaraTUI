<?php

namespace LaraTui\State;

use Brick\JsonMapper\JsonMapper;
use Brick\JsonMapper\OnMissingProperties;
use LaraTui\Mapper\CamelCaseToKebabCaseMapper;
use LaraTui\Mapper\KebabCaseToCamelCaseMapper;

class OutdatedPackages
{
    /** @param Package[] $installed */
    public function __construct(
        public array $installed,
    ) {}

    public static function fromJson(string $data): self
    {
        return (new JsonMapper(
            onMissingProperties: OnMissingProperties::SET_NULL,
            jsonToPhpNameMapper: new KebabCaseToCamelCaseMapper(),
            phpToJsonNameMapper: new CamelCaseToKebabCaseMapper(),
        ))->map($data, self::class);
    }
}

<?php

namespace LaraTui\State;

use Brick\JsonMapper\JsonMapper;
use Brick\JsonMapper\OnExtraProperties;
use Brick\JsonMapper\OnMissingProperties;

class PHPVersions
{
    /** @param PHPVersion[] $data */
    public function __construct(
        public readonly array $data,
    ) {}

    public static function fromResponseBody(string $data): self
    {
        $data = json_decode($data, true);
        $data['data'] = array_values($data['data']);
        $data = json_encode($data);

        return (new JsonMapper(
            onMissingProperties: OnMissingProperties::SET_NULL,
            onExtraProperties: OnExtraProperties::IGNORE,
        ))
            ->map($data, self::class);
    }
}

<?php

namespace LaraTui\State;

class PHPVersions
{
    public readonly array $data;

    public static function fromResponseBody(string $data): self
    {
        $jsonData = json_decode($data, true);
        $data = array_map(function ($phpVersion) {
            return PHPVersion::fromArray($phpVersion);
        }, array_values($jsonData['data']));

        return new self($data);
    }
}

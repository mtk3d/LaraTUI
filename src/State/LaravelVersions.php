<?php

namespace LaraTui\State;

class LaravelVersions
{
    public function __construct(
        public readonly array $data,
    ) {}

    public static function fromResponseBody(string $data): self
    {
        $jsonData = json_decode($data, true);
        $data = array_map(fn ($laravelVersion) => new LaravelVersion($laravelVersion), $jsonData['data']);

        return new self($data);
    }
}

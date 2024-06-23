<?php

namespace LaraTui\State;

use Spatie\DataTransferObject\DataTransferObject;

class Package extends DataTransferObject
{
    public string $name;

    public ?bool $directDependency;

    public ?string $homepage;

    public string $source;

    public string $version;

    public string $description;

    public bool $abandoned;

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        return new self($data);
    }
}

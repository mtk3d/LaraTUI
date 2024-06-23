<?php

namespace LaraTui\State;

use Spatie\DataTransferObject\DataTransferObject;

class LaravelVersion extends DataTransferObject
{
    public int $major;

    public ?int $latest_minor;

    public int $latest_patch;

    public string $latest;

    public string $released_at;

    public ?string $ends_bugfixes_at;

    public ?string $ends_securityfixes_at;

    public array $supported_php;

    public string $status;
}

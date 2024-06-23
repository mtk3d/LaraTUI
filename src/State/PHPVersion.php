<?php

namespace LaraTui\State;

use Spatie\DataTransferObject\DataTransferObject;

class PHPVersion extends DataTransferObject
{
    public int $versionId;

    public string $name;

    public string $releaseDate;

    public string $activeSupportEndDate;

    public string $eolDate;

    public bool $isEOLVersion;

    public bool $isSecureVersion;

    public bool $isLatestVersion;

    public bool $isFutureVersion;

    public string $statusLabel;

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}

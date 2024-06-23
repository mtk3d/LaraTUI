<?php

namespace LaraTui\State;

use Spatie\DataTransferObject\DataTransferObject;

class InstalledPackages extends DataTransferObject
{
    public array $installed;

    public static function fromJson(string $data): self
    {
        $jsonData = json_decode($data, true);
        $jsonData['installed'] = array_map(fn ($packageData) => new Package($packageData), $jsonData['installed']);

        return new self($jsonData);
    }
}

<?php

namespace LaraTui\State;

use Illuminate\Support\Str;

class MigrationStatus
{
    public function __construct(
        public readonly ?int $pending,
        public readonly int $all,
    ) {}

    public static function fromMigrationStatusCommand(string $output): self
    {
        $count = Str::of($output)
            ->trim()
            ->explode(PHP_EOL)
            ->skip(1)
            ->countBy(fn ($line) => Str::endsWith($line, 'Pending') ? 'Pending' : 'Ran');

        return new self(
            $count->get('Pending') ?? 0,
            $count->get('Ran') + $count->get('Pending') ?? 0,
        );
    }
}

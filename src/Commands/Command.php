<?php

namespace LaravelSailTui\Commands;

use LaravelSailTui\State;
use React\EventLoop\LoopInterface;

abstract class Command
{
    public static string $commandName;

    public function __construct(
        protected readonly State $state,
        protected readonly LoopInterface $loop
    ) {
        $this->init();
    }

    protected function init(): void
    {}

    abstract function execute(array $data): void;
}

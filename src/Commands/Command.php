<?php

namespace LaraTui\Commands;

use LaraTui\CommandBus;
use LaraTui\EventBus;
use LaraTui\State;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

abstract class Command
{
    private array $outputs = [];

    public function __construct(
        protected readonly State $state,
        protected readonly LoopInterface $loop,
        protected readonly Browser $browser,
        protected readonly EventBus $eventBus,
        protected readonly CommandBus $commandBus,
    ) {
        $this->init();
    }

    protected function init(): void {}

    abstract public function execute(array $data): void;

    protected function execCommand(string $command): PromiseInterface
    {
        $deffered = new Deferred();
        $process = new Process($command);
        $process->start($this->loop);
        $this->outputs[$command] = '';

        $process->stdout->on('data', function ($chunk) use ($command) {
            $this->outputs[$command] .= $chunk;
        });

        $process->stderr->on('data', function ($chunk) use ($command) {
            $this->outputs[$command] .= $chunk;
        });

        $process->stdout->on('end', function () use ($deffered, $command) {
            $deffered->resolve($this->outputs[$command]);
        });

        return $deffered->promise();
    }
}

<?php

namespace LaraTui\Commands;

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

        $process->stdout->on('data', function ($chank) use ($command) {
            $this->outputs[$command] .= $chank;
        });

        $process->stdout->on('end', function () use ($deffered, $command) {
            $deffered->resolve($this->outputs[$command]);
        });

        return $deffered->promise();
    }
}

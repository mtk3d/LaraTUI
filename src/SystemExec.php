<?php

namespace LaraTui;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class SystemExec
{
    private array $outputs = [];

    public function __construct(
        private readonly LoopInterface $loop
    ) {}

    public function __invoke(string ...$command): PromiseInterface
    {
        $key = uniqid(implode('-', $command));
        $command = implode(' ', array_map('escapeshellarg', $command));

        $deffered = new Deferred();
        $process = new Process($command);
        $process->start($this->loop);
        $this->outputs[$key] = '';

        $process->stdout->on('data', function ($chunk) use ($key) {
            $this->outputs[$key] .= $chunk;
        });

        $process->stderr->on('data', function ($chunk) use ($key) {
            $this->outputs[$key] .= $chunk;
        });

        $process->stdout->on('end', function () use ($deffered, $key) {
            $deffered->resolve($this->outputs[$key]);
        });

        return $deffered->promise();
    }
}

<?php

namespace LaraTui;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class SystemExec
{
    private static array $processes = [];

    public function __construct(
        private readonly LoopInterface $loop,
        private readonly State $state,
    ) {}

    /**
     * @param  string[]  $command
     */
    public function __invoke(array $command, ?string $stateKey = null): PromiseInterface
    {
        $key = $stateKey ?? uniqid(implode('-', $command));
        $command = implode(' ', array_map('escapeshellarg', $command));

        $deffered = new Deferred();
        $process = new Process($command);
        self::$processes[$command] = $process;
        $process->start($this->loop);
        $this->state->set($key, '');

        $process->stdout->on('data', function ($chunk) use ($key) {
            $this->state->append($key, $chunk);
        });

        $process->stderr->on('data', function ($chunk) use ($key) {
            $this->state->append($key, $chunk);
        });

        $process->stdout->on('end', function () use ($deffered, $command, $key) {
            $deffered->resolve($this->state->get($key));
            unset(self::$processes[$command]);
        });

        $process->on('exit', function () use ($deffered, $key) {
            $deffered->resolve($this->state->get($key));
        });

        return $deffered->promise();
    }

    public function terminateAll(): void
    {
        foreach (self::$processes as $process) {
            $process->terminate();
        }
    }
}

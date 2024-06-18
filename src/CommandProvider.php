<?php

namespace LaraTui;

use LaraTui\Commands\GetProjectNameCommand;
use LaraTui\Commands\OutdatedPackagesCommand;
use LaraTui\Commands\SailContainersCommand;
use LaraTui\Commands\ServicesStatusCommand;
use React\EventLoop\LoopInterface;

class CommandProvider
{
    public function __construct(
        protected readonly State $state,
        protected readonly LoopInterface $loop,
        protected readonly CommandBus $commandBus,
    ) {}

    public function boot(): void
    {
        $this->register(OutdatedPackagesCommand::class);
        $this->register(SailContainersCommand::class);
        $this->register(ServicesStatusCommand::class);
        $this->register(GetProjectNameCommand::class);
    }

    private function register(string $className): void
    {
        /** @var Command $command **/
        $command = new $className($this->state, $this->loop);
        $this->commandBus->reactTo($command::$commandName, [$command, 'execute']);
    }
}

<?php

namespace LaraTui;

use LaraTui\Commands\FetchComposerVersionsCommand;
use LaraTui\Commands\FetchLaravelVersionsCommand;
use LaraTui\Commands\FetchPHPVersionsCommand;
use LaraTui\Commands\FetchVersionsInfoCommand;
use LaraTui\Commands\GetProjectNameCommand;
use LaraTui\Commands\OutdatedPackagesCommand;
use LaraTui\Commands\SailContainersCommand;
use LaraTui\Commands\ServicesStatusCommand;
use React\EventLoop\LoopInterface;
use React\Http\Browser;

class CommandProvider
{
    public function __construct(
        protected readonly State $state,
        protected readonly LoopInterface $loop,
        protected readonly CommandBus $commandBus,
        protected readonly EventBus $eventBus,
        protected readonly Browser $browser,
    ) {}

    public function boot(): void
    {
        $this->register(OutdatedPackagesCommand::class);
        $this->register(SailContainersCommand::class);
        $this->register(ServicesStatusCommand::class);
        $this->register(GetProjectNameCommand::class);
        $this->register(FetchLaravelVersionsCommand::class);
        $this->register(FetchComposerVersionsCommand::class);
        $this->register(FetchPHPVersionsCommand::class);
        $this->register(FetchVersionsInfoCommand::class);
    }

    private function register(string $className): void
    {
        /** @var Command $command * */
        $command = new $className($this->state, $this->loop, $this->browser, $this->eventBus);
        $this->commandBus->reactTo($command::class, [$command, 'execute']);
    }
}

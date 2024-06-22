<?php

namespace LaraTui;

use LaraTui\Commands\GetProjectNameCommand;
use LaraTui\Windows\Main;
use LaraTui\Windows\Window;
use PhpTui\Term\Actions;
use PhpTui\Term\EventParser;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\DisplayBuilder;
use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;

class Application
{
    private Window $window;
    private EventParser $eventParser;

    private function __construct(
        private LoopInterface $loop,
        private Terminal $terminal,
        private Display $display,
        private EventBus $eventBus,
        private CommandBus $commandBus,
        private State $state,
    ) {
        $this->eventParser = new EventParser();
    }

    public static function new(): self
    {
        $loop = Loop::get();
        $eventBus = new EventBus();
        $commandBus = new CommandBus();
        $state = new State();
        $commandProvider = new CommandProvider($state, $loop, $commandBus);
        $commandProvider->boot();

        return new self(
            $loop,
            Terminal::new(),
            DisplayBuilder::default()->build(),
            $eventBus,
            $commandBus,
            $state,
        );
    }

    public function run(): int
    {
        $this->terminal->execute(Actions::alternateScreenEnable());
        $this->terminal->execute(Actions::cursorHide());
        $this->terminal->execute(Actions::enableMouseCapture());
        $this->terminal->enableRawMode();
        $this->terminal->execute(Actions::moveCursor(0, 0));
        $this->display->clear();
        $this->init();
        $this->startRendering();
        $this->startInputHandling();
        $this->loop->run();

        return 0;
    }

    public function init(): void
    {
        $this->commandBus
            ->dispatch(GetProjectNameCommand::$commandName);

        $this->window = new Main();
        $this->window->registerWindow($this->loop, $this->eventBus, $this->commandBus, $this->state);
    }

    public function startRendering(): void
    {
        $this->loop->addPeriodicTimer(1 / 60, function () {
            $this->display->draw(
                $this->window->render(),
            );
        });
    }

    public function startInputHandling(): void
    {
        $stdin = new ReadableResourceStream(STDIN, $this->loop);

        $stdin->on('data', function ($data) {
            $this->eventParser->advance($data, false);

            foreach ($this->eventParser->drain() as $event) {
                $this->eventBus->emit($event);
            }

            if ($data === 'q') {
                $this->terminal->disableRawMode();
                $this->terminal->execute(Actions::disableMouseCapture());
                $this->terminal->execute(Actions::cursorShow());
                $this->terminal->execute(Actions::alternateScreenDisable());
                $this->loop->stop();
            }
            if ($data === 'u') {
                $process = new Process('docker compose up -d');
                $this->state->delete('output_log');

                $process->start($this->loop);
                $process->stdout->on('data', function ($chunk) {
                    $this->state->append('output_log', $chunk);
                });
                $process->stderr->on('data', function ($chunk) {
                    $this->state->append('output_log', $chunk);
                });
            }

            if ($data === 'm') {
                $process = new Process('php artisan migrate');
                $this->state->delete('output_log');

                $process->start($this->loop);
                $process->stdout->on('data', function ($chunk) {
                    $this->state->append('output_log', $chunk);
                });
                $process->stderr->on('data', function ($chunk) {
                    $this->state->append('output_log', $chunk);
                });
            }
        });
    }
}

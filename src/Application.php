<?php

namespace LaraTui;

use LaraTui\Commands\GetProjectNameCommand;
use LaraTui\Windows\Main;
use LaraTui\Windows\Window;
use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;

class Application
{
    private Window $window;

    private EventParser $eventParser;

    public function __construct(
        private LoopInterface $loop,
        private Terminal $terminal,
        private Display $display,
        private EventBus $eventBus,
        private CommandInvoker $commandInvoker,
        private State $state,
    ) {
        $this->eventParser = new EventParser();
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
        $this->commandInvoker->invoke(new GetProjectNameCommand());

        $this->window = new Main();
        $this->window->registerWindow($this->loop, $this->eventBus, $this->state, $this->commandInvoker);
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
                if (in_array($event::class, [CharKeyEvent::class, CodedKeyEvent::class])) {
                    $this->eventBus->emit($event);
                }
            }

            if ($data === 'q') {
                $this->terminal->disableRawMode();
                $this->terminal->execute(Actions::disableMouseCapture());
                $this->terminal->execute(Actions::cursorShow());
                $this->terminal->execute(Actions::alternateScreenDisable());
                $this->loop->stop();
            }
        });
    }
}

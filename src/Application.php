<?php

namespace LaraTui;

use DI\Container;
use LaraTui\Windows\Main;
use LaraTui\Windows\Popup;
use LaraTui\Windows\Window;
use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;

class Application
{
    private Window $window;

    private Popup $popup;

    private EventParser $eventParser;

    public function __construct(
        private LoopInterface $loop,
        private Terminal $terminal,
        private Display $display,
        private EventBus $eventBus,
        private CommandInvoker $commandInvoker,
        private State $state,
        private Container $container,
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
        $this->window = $this->container->make(Main::class);
        $this->popup = $this->container->make(Popup::class);
    }

    public function startRendering(): void
    {
        $this->loop->addPeriodicTimer(1 / 60, function () {
            $this->display->draw(
                CompositeWidget::fromWidgets(
                    $this->window->render(),
                    $this->popup->isActive() ? $this->popup->render() : BlockWidget::default(),
                ),
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

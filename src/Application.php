<?php

namespace LaravelSailTui;

use LaravelSailTui\Commands\GetProjectNameCommand;
use LaravelSailTui\Panes\LaravelVersions;
use LaravelSailTui\Panes\OutdatedPackages;
use LaravelSailTui\Panes\OutputLog;
use LaravelSailTui\Panes\Project;
use LaravelSailTui\Panes\Services;
use PhpTui\Term\Actions;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;

class Application
{
    private array $sidebarPanes = [];
    private array $mainPanes = [];
    private int $selectedPane = 0;

    private function __construct(
        private LoopInterface $loop,
        private Terminal $terminal,
        private Display $display,
        private EventBus $eventBus,
        private CommandBus $commandBus,
        private State $state,
    ) {
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

        $this->sidebarPanes[] = new LaravelVersions();
        $this->sidebarPanes[] = new Services();
        $this->sidebarPanes[] = new OutdatedPackages();
        $this->mainPanes[] = new Project();
        $this->mainPanes[] = new OutputLog();
        $this->mainPanes[] = new OutputLog();

        foreach ([...$this->sidebarPanes, ...$this->mainPanes] as $pane) {
            $pane->registerPane(
                $this->loop,
                $this->eventBus,
                $this->commandBus,
                $this->state
            );
        }
    }

    public function resetPaneSelection(): void
    {
        foreach ([...$this->sidebarPanes, ...$this->mainPanes] as $pane) {
            $pane->deselectPane();
        }
    }

    public function startRendering(): void
    {
        $this->loop->addPeriodicTimer(1 / 60, function () {
            $this->display->draw(
                GridWidget::default()
                    ->direction(Direction::Horizontal)
                    ->constraints(
                        Constraint::percentage(30),
                        Constraint::percentage(70),
                    )
                    ->widgets(
                        GridWidget::default()
                            ->direction(Direction::Vertical)
                            ->constraints(
                                Constraint::length(3),
                                Constraint::percentage(50),
                                Constraint::percentage(50),
                            )->widgets(
                                ...array_map(fn ($pane) => $pane->render(), $this->sidebarPanes),
                            ),
                        $this->mainPanes[$this->selectedPane]->render(),
                    )
            );
        });
    }

    public function startInputHandling(): void
    {

        $stdin = new ReadableResourceStream(STDIN, $this->loop);

        $stdin->on('data', function ($data) {
            $this->eventBus->emit($data);

            if ($data === 'q') {
                $this->terminal->disableRawMode();
                $this->terminal->execute(Actions::disableMouseCapture());
                $this->terminal->execute(Actions::cursorShow());
                $this->terminal->execute(Actions::alternateScreenDisable());
                $this->loop->stop();
            }
            if (strpos($data, "\t") !== false) { // Tab
                if ($this->selectedPane < count($this->sidebarPanes) - 1) {
                    $this->selectedPane++;
                } else {
                    $this->selectedPane = 0;
                }

                $this->resetPaneSelection();
                $this->sidebarPanes[$this->selectedPane]->selectPane();
            }
            if ($data === "\033[Z") { // Shift tab
                if ($this->selectedPane > 0) {
                    $this->selectedPane--;
                } else {
                    $this->selectedPane = count($this->sidebarPanes) - 1;
                }
                $this->resetPaneSelection();
                $this->sidebarPanes[$this->selectedPane]->selectPane();
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

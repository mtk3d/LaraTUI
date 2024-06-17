<?php

declare(strict_types=1);

use LaravelSailTui\CommandBus;
use LaravelSailTui\CommandProvider;
use LaravelSailTui\Commands\GetProjectNameCommand;
use LaravelSailTui\DockerClient\Docker;
use LaravelSailTui\EventBus;
use LaravelSailTui\Logger;
use LaravelSailTui\Panes\LaravelVersions;
use LaravelSailTui\Panes\OutdatedPackages;
use LaravelSailTui\Panes\OutputLog;
use LaravelSailTui\Panes\Services;
use LaravelSailTui\Panes\Project;
use LaravelSailTui\State;
use PhpTui\Term\Actions;
use PhpTui\Term\Terminal;
use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\Buffer\BufferContext;
use PhpTui\Tui\Extension\Core\Widget\BufferWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Position\Position;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Direction;
use Psr\Http\Message\ResponseInterface;
use React\ChildProcess\Process;
use React\Http\Message\ResponseException;
use React\Stream\ReadableResourceStream;

require __DIR__ . '/vendor/autoload.php';

$terminal = Terminal::new();
$terminal->execute(Actions::alternateScreenEnable());
$terminal->execute(Actions::cursorHide());
$terminal->execute(Actions::enableMouseCapture());
$terminal->enableRawMode();
$terminal->execute(Actions::moveCursor(0, 0));

$loop = React\EventLoop\Loop::get();

$eventBus = new EventBus();
$commandBus = new CommandBus();
$state = new State();
$commandProvider = new CommandProvider($state, $loop, $commandBus);
$commandProvider->boot();
Logger::init($state);

$commandBus->dispatch(GetProjectNameCommand::$commandName);

$state->set('_debug', false);

$display = DisplayBuilder::default()->build();
$display->clear();

$docker = new Docker();

$srv = new Services();
$srv->registerPane($loop, $eventBus, $commandBus, $state);
$lvr = new LaravelVersions();
$lvr->registerPane($loop, $eventBus, $commandBus, $state);
$ol = new OutputLog();
$ol->registerPane($loop, $eventBus, $commandBus, $state);
$pp = new Project();
$pp->registerPane($loop, $eventBus, $commandBus, $state);
$op = new OutdatedPackages();
$op->registerPane($loop, $eventBus, $commandBus, $state);

$lvr->selectPane();

$panes = [$lvr, $srv, $op];
$views = [$pp, $ol, $ol];

$paneSelected = 0;
$resetSelection = function () use ($panes) {
    foreach ($panes as $pane) {
        $pane->deselectPane();
    }
};

$getMainView = fn ($s) => $views[$s];

$loop->addPeriodicTimer(1 / 60, function () use ($display, $state, $getMainView, &$paneSelected, $lvr, $srv, $op) {
    $display->draw(
        CompositeWidget::fromWidgets(
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
                            $lvr->render(),
                            $srv->render(),
                            $op->render(),
                        ),
                    $getMainView($paneSelected)->render(),
                ),
            BufferWidget::new(function (BufferContext $context) use ($state) {
                if ($state->get('_debug', false)) {
                    $context->draw(BlockWidget::default());
                    $context->buffer->putSpan(Position::at(5, 2), Span::fromString($state->get('_log', 'Debug mode on'))->yellow()->onDarkGray(), 20);
                }
            }),
        ),
    );
});


$stdin = new ReadableResourceStream(STDIN, $loop);
$process = null;


$stdin->on('data', function ($data) use ($panes, $resetSelection, $eventBus, $terminal, $loop, &$paneSelected, $state, $process, &$mainView, $views) {
    $eventBus->emit($data);

    if ($data === 'q') {
        $terminal->disableRawMode();
        $terminal->execute(Actions::disableMouseCapture());
        $terminal->execute(Actions::cursorShow());
        $terminal->execute(Actions::alternateScreenDisable());
        $loop->stop();
    }
    if (strpos($data, "\t") !== false) { // Tab
        if ($paneSelected < count($panes) - 1) {
            $paneSelected++;
        } else {
            $paneSelected = 0;
        }

        $resetSelection();
        $panes[$paneSelected]->selectPane();
        $mainView = $views[$paneSelected];
    }
    if ($data === "\033[Z") { // Shift tab
        if ($paneSelected > 0) {
            $paneSelected--;
        } else {
            $paneSelected = count($panes) - 1;
        }
        $resetSelection();
        $panes[$paneSelected]->selectPane();
        $mainView = $views[$paneSelected];
    }
    if ($data === 'u') {
        $process = new Process('docker compose up -d');
        $state->delete('output_log');

        $process->start($loop);
        $process->stdout->on('data', function ($chunk) use ($state) {
            $state->append('output_log', $chunk);
        });
        $process->stderr->on('data', function ($chunk) use ($state) {
            $state->append('output_log', $chunk);
        });
    }

    if ($data === 'm') {
        $process = new Process('php artisan migrate');
        $state->delete('output_log');

        $process->start($loop);
        $process->stdout->on('data', function ($chunk) use ($state) {
            $state->append('output_log', $chunk);
        });
        $process->stderr->on('data', function ($chunk) use ($state) {
            $state->append('output_log', $chunk);
        });
    }
});


$loop->run();

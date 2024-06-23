<?php

namespace LaraTui\Windows;

use LaraTui\Commands\FetchComposerVersionsCommand;
use LaraTui\Commands\FetchLaravelVersionsCommand;
use LaraTui\Commands\FetchPHPVersionsCommand;
use LaraTui\Panes\LaravelVersions;
use LaraTui\Panes\OutdatedPackages;
use LaraTui\Panes\Project;
use LaraTui\Panes\Services;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class Main extends Window
{
    protected array $panes = [
        LaravelVersions::class,
        Services::class,
        OutdatedPackages::class,
        Project::class,
    ];

    public function init(): void
    {
        $this->commandBus->dispatch(FetchPHPVersionsCommand::class);
        $this->commandBus->dispatch(FetchLaravelVersionsCommand::class);
        $this->commandBus->dispatch(FetchComposerVersionsCommand::class);
        array_values($this->panesInstances)[0]->selectPane();
    }

    public function render(): Widget
    {
        return GridWidget::default()
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
                        $this->renderPane(LaravelVersions::class),
                        $this->renderPane(Services::class),
                        $this->renderPane(OutdatedPackages::class),
                    ),
                $this->renderPane(Project::class),
            );
    }
}

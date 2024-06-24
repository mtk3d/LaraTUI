<?php

namespace LaraTui\Windows;

use LaraTui\Panes\OutdatedPackages;
use LaraTui\Panes\Project;
use LaraTui\Panes\ProjectView;
use LaraTui\Panes\Services;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class Main extends Window
{
    protected array $panes = [
        Project::class,
        Services::class,
        OutdatedPackages::class,
        ProjectView::class,
    ];

    public function init(): void
    {
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
                        $this->renderPane(Project::class),
                        $this->renderPane(Services::class),
                        $this->renderPane(OutdatedPackages::class),
                    ),
                $this->renderPane(ProjectView::class),
            );
    }
}

<?php

namespace LaraTui\Windows;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\Panes\OutdatedPackages;
use LaraTui\Panes\OutputLog;
use LaraTui\Panes\Project;
use LaraTui\Panes\ProjectView;
use LaraTui\Panes\Services;
use PhpTui\Term\KeyCode;
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
        OutputLog::class,
    ];

    private array $panesNavigation = [
        Project::class => ProjectView::class,
        Services::class => OutputLog::class,
        OutdatedPackages::class => OutputLog::class,
    ];

    private string $mainPane = ProjectView::class;

    private int $selectedPane = 0;

    private bool $isMainSelected = false;

    public function init(): void
    {
        $firstSidebarPaneClass = array_keys($this->panesNavigation)[0];
        $this->panesInstances[$firstSidebarPaneClass]->selectPane();
    }

    #[KeyPressed(KeyCode::Tab, true)]
    public function nextPane(): void
    {
        $this->isMainSelected = false;
        if ($this->selectedPane < count($this->panesNavigation) - 1) {
            $this->selectedPane++;
        } else {
            $this->selectedPane = 0;
        }

        $this->setPaneSelection();
    }

    #[KeyPressed(KeyCode::BackTab, true)]
    public function previousPane(): void
    {
        $this->isMainSelected = false;
        if ($this->selectedPane > 0) {
            $this->selectedPane--;
        } else {
            $this->selectedPane = count($this->panesInstances) - 1;
        }
        $this->setPaneSelection();
    }

    #[KeyPressed(KeyCode::Enter, true)]
    public function enterMainPane(): void
    {
        $this->isMainSelected = true;
        $this->setPaneSelection();
    }

    #[KeyPressed(KeyCode::Esc, true)]
    public function exitMainPane(): void
    {
        $this->isMainSelected = false;
        $this->setPaneSelection();
    }

    public function setPaneSelection(): void
    {
        foreach ($this->panesInstances as $pane) {
            $pane->deselectPane();
        }

        $selectedPaneClass = array_keys($this->panesNavigation)[$this->selectedPane];

        if (! $this->isMainSelected) {
            $this->panesInstances[$selectedPaneClass]->selectPane();
            $this->mainPane = $this->panesNavigation[$selectedPaneClass];
        } else {
            $this->panesInstances[$this->mainPane]->selectPane();
        }
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
                $this->renderPane($this->mainPane),
            );
    }
}

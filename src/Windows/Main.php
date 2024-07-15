<?php

namespace LaraTui\Windows;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\Panes\OutdatedPackages;
use LaraTui\Panes\OutputLog;
use LaraTui\Panes\Project;
use LaraTui\Panes\ProjectView;
use LaraTui\Panes\Services;
use PhpTui\Term\KeyCode;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Layout;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class Main extends Window
{
    protected array $components = [
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

    public function mount(): void
    {
        $firstSidebarPaneClass = array_keys($this->panesNavigation)[0];
        $this->componentInstances[$firstSidebarPaneClass]->activate();
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
            $this->selectedPane = count($this->panesNavigation) - 1;
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
        foreach ($this->componentInstances as $pane) {
            $pane->deactivate();
        }

        $selectedPaneClass = array_keys($this->panesNavigation)[$this->selectedPane];

        if (! $this->isMainSelected) {
            $this->componentInstances[$selectedPaneClass]->activate();
            $this->mainPane = $this->panesNavigation[$selectedPaneClass];
        } else {
            $this->componentInstances[$this->mainPane]->activate();
        }
    }

    public function render(Area $area): Widget
    {
        $horizontalConstraints = [
            Constraint::percentage(30),
            Constraint::percentage(70),
        ];
        $horizontalLayout = Layout::default()
            ->direction(Direction::Horizontal)
            ->constraints($horizontalConstraints)
            ->split($area);

        $verticalConstraints = [
            Constraint::length(3),
            Constraint::percentage(50),
            Constraint::percentage(50),
        ];
        $verticalLayout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints($verticalConstraints)
            ->split($horizontalLayout->get(0));

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(...$horizontalConstraints)
            ->widgets(
                GridWidget::default()
                    ->direction(Direction::Vertical)
                    ->constraints(...$verticalConstraints)
                    ->widgets(
                        $this->renderComponent(Project::class, $verticalLayout->get(0)),
                        $this->renderComponent(Services::class, $verticalLayout->get(1)),
                        $this->renderComponent(OutdatedPackages::class, $verticalLayout->get(2)),
                    ),
                $this->renderComponent($this->mainPane, $horizontalLayout->get(1)),
            );
    }
}

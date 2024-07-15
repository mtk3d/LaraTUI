<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\Mouse;
use LaraTui\PaneTabs\ProjectArtisan;
use LaraTui\PaneTabs\ProjectCondition;
use LaraTui\PaneTabs\ProjectEnvs;
use LaraTui\PaneTabs\ProjectLogs;
use LaraTui\Traits\TabManager;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class ProjectView extends Pane
{
    use TabManager;

    protected array $components = [
        ProjectCondition::class,
        ProjectLogs::class,
        ProjectArtisan::class,
        ProjectEnvs::class,
    ];

    protected array $tabs = [];

    public function init(): void
    {
        $this->tabs = [
            Line::fromString('  Condition '),
            Line::fromString('  Logs '),
            Line::fromString('  Artisan '),
            Line::fromString(' 󰯷 envs'),
        ];
    }

    public function mount(): void
    {
        $this->getComponent(ProjectCondition::class)->activate();
    }

    public function tabs(): array
    {
        return $this->tabs;
    }

    public function setTab(int $tab): void
    {
        foreach ($this->componentInstances as $component) {
            $component->deactivate();
        }

        $this->getComponent($this->components[$tab])->activate();
    }

    public function titles(): array
    {
        $titlesObjs = [];

        foreach ($this->tabs() as $index => $title) {
            $title->patchStyle(Style::default()->white());
            if ($index === $this->currentTab) {
                $title->red();
            }
            $titlesObjs[] = Title::fromLine($title);
        }

        return $titlesObjs;
    }

    #[Mouse(true)]
    public function clickOnItem(array $data): void
    {
        if (! $this->area) {
            return;
        }

        $event = $data['event'];

        if ($event->kind !== MouseEventKind::Down) {
            return;
        }

        if ($event->row !== $this->area->top()) {
            return;
        }

        $i = $this->getTabFromLeftSide($event->column);

        if (null === $i) {
            return;
        }

        $this->currentTab = $i;
        $this->setTab($i);
    }

    private function getTabFromLeftSide(int $left): ?int
    {
        $clickInWindow = $left - $this->area->left();
        if ($clickInWindow < 0) {
            return null;
        }

        $fromLeft = 2;
        $item = 0;
        foreach ($this->tabs as $line) {
            $fromLeft += $line->width();
            if ($fromLeft > $clickInWindow) {
                return $item;
            }
            $fromLeft++;
            $item++;
        }

        return min(max($item, 3), 0);
    }

    public function render(Area $area): Widget
    {
        $this->area = $area;

        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->titles(...$this->titles())
                ->borderStyle($this->isActive ? Style::default()->red() : Style::default())
                ->widget(
                    match ($this->currentTab) {
                        0 => $this->renderComponent(ProjectCondition::class, $area),
                        1 => $this->renderComponent(ProjectLogs::class, $area),
                        2 => $this->renderComponent(ProjectArtisan::class, $area),
                        3 => $this->renderComponent(ProjectEnvs::class, $area),
                    }
                );
    }
}

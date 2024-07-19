<?php

namespace LaraTui\PaneTabs;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Mouse;
use LaraTui\Commands\TailLogsCommand;
use LaraTui\Component;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use PhpTui\Tui\Extension\Core\Widget\List\ListItem;
use PhpTui\Tui\Extension\Core\Widget\ListWidget;
use PhpTui\Tui\Extension\Core\Widget\Scrollbar\ScrollbarOrientation;
use PhpTui\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use PhpTui\Tui\Extension\Core\Widget\ScrollbarWidget;
use PhpTui\Tui\Widget\Corner;
use PhpTui\Tui\Widget\Widget;

class ProjectLogs extends Component
{
    private Display $display;

    private Area $area;

    private int $offset = 0;

    public function init(Display $display): void
    {
        $this->display = $display;
        $this->execute(new TailLogsCommand());
    }

    #[KeyPressed('j')]
    #[KeyPressed(KeyCode::Down)]
    public function down(): void
    {
        if ($this->offset > 0) {
            $this->offset--;
        }
    }

    #[KeyPressed('k')]
    #[KeyPressed(KeyCode::Up)]
    public function up(): void
    {
        $this->offset++;
    }

    #[KeyPressed('d')]
    public function downCtrl(array $data): void
    {
        if (! isset($data['modifiers']) || $data['modifiers'] !== KeyModifiers::CONTROL) {
            return;
        }

        $half = $this->halfScreen();
        if ($this->offset - $half > 0) {
            $this->offset -= $this->halfScreen();

            return;
        }

        $this->offset = 0;
    }

    #[KeyPressed('u')]
    public function upCtrl(array $data): void
    {
        if (! isset($data['modifiers']) || $data['modifiers'] !== KeyModifiers::CONTROL) {
            return;
        }

        $half = $this->halfScreen();
        $this->offset += $half;
    }

    #[Mouse()]
    public function scroll(array $data): void
    {
        if (! isset($this->area)) {
            return;
        }

        $event = $data['event'];

        switch ($event->kind) {
            case MouseEventKind::ScrollUp:
                $this->offset++;
                break;
            case MouseEventKind::ScrollDown:
                $this->offset--;
                break;
        }
    }

    private function halfScreen(): int
    {
        $height = $this->display->viewportArea()->height - 2;

        return (int) ($height / 2);
    }

    public function render(Area $area): Widget
    {
        $this->area = $area;
        $logs = $this->state->get('app_log', '');
        $items = explode(PHP_EOL, $logs);
        $listItems = array_map(
            function (string $line) use ($area): array {
                $lines = explode(PHP_EOL, wordwrap($line, $area->width - 3, PHP_EOL, true));
                $lines = array_map([ListItem::class, 'fromString'], $lines);
                return $lines;
            },
            array_reverse($items),
        );

        $listItems = array_merge(...$listItems);

        $viewportHeight = $area->height;

        $linesCount = count($listItems);
        if ($this->offset + $viewportHeight > $linesCount) {
            $this->offset = $linesCount - $viewportHeight;
        }

        if ($this->offset < 0) {
            $this->offset = 0;
        }

        $scrollContentLength = $linesCount - $viewportHeight;

        return CompositeWidget::fromWidgets(
            ListWidget::default()
                ->startCorner(Corner::BottomLeft)
                ->items(...$listItems)
                ->offset($this->offset),
            ScrollbarWidget::default()
                ->state(new ScrollbarState($scrollContentLength, $scrollContentLength - $this->offset, $viewportHeight))
                ->orientation(ScrollbarOrientation::VerticalRight),
        );
    }
}

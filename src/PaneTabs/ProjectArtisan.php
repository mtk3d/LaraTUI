<?php

namespace LaraTui\PaneTabs;

use ArrayIterator;
use Iterator;
use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Mouse;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\RunArtisanCommand;
use LaraTui\Component;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\Buffer\BufferContext;
use PhpTui\Tui\Extension\Core\Widget\BufferWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\List\ListItem;
use PhpTui\Tui\Extension\Core\Widget\ListWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Layout;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Corner;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class ProjectArtisan extends Component
{
    private bool $typing = false;

    private bool $cursorVisible = true;

    private Iterator $history;

    private string $line = '';

    private Area $inputArea;

    public function init(): void
    {
        $this->history = new ArrayIterator([]);
        $this->eventBus->listen('input', fn (array $data) => $this->line .= $data['data']);
        $this->eventBus->listen('typing_mode', fn (array $data) => $this->typing = $data['state']);
    }

    #[KeyPressed(KeyCode::Enter)]
    public function input(): void
    {
        if ($this->typing) {
            $this->execute(new RunArtisanCommand($this->line));
            $this->history = new ArrayIterator([
                $this->line,
                ...iterator_to_array($this->history),
            ]);
            $this->line = '';
            $this->history->rewind();
        } else {
        }

    }

    #[KeyPressed(KeyCode::Enter)]
    public function enterTypingMode(): void
    {
        $this->eventBus->emit('typing_mode', ['state' => true]);
    }

    #[KeyPressed(KeyCode::Up)]
    public function previousCommand(): void
    {
        if ($this->history->valid()) {
            $this->line = $this->history->current();
        }
        $this->history->next();
    }

    #[KeyPressed(KeyCode::Backspace)]
    public function backspace(): void
    {
        $this->line = substr($this->line, 0, -1);
    }

    #[Periodic(0.8)]
    public function cursor(): void
    {
        if ($this->typing) {
            $this->cursorVisible = ! $this->cursorVisible;
        }
    }

    #[Mouse()]
    public function click(array $data): void
    {
        if (!isset($this->inputArea)) {
            return;
        }

        $ia = $this->inputArea;

        /** @var MouseEvent $event */
        $event = $data['event'];

        if ($event->kind !== MouseEventKind::Down) {
            return;
        }

        $e = $event;

        if ($e->row > $ia->top() && $e->row < $ia->bottom() && $e->column > $ia->left() && $e->column < $ia->right()) {
            $this->enterTypingMode();
        }
    }

    public function render(Area $area): Widget
    {
        $style = Style::default();

        if ($this->typing) {
            $style = $style->red();
        }

        $line = ' > <fg=darkgray>php artisan </>';

        $line .= $this->line;

        if ($this->cursorVisible) {
            $line .= '▓';
        }

        $logs = $this->state->get('artisan_command', '');
        $items = explode(PHP_EOL, $logs);
        $listItems = array_map(
            fn (string $line): ListItem => ListItem::fromString($line),
            array_reverse($items),
        );

        $constraints = [
                Constraint::length($area->height - 5),
                Constraint::length(3),
            ];

        $layout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints($constraints)
            ->split($area);

        $this->inputArea = $layout->get(1);

        return
            GridWidget::default()
                ->direction(Direction::Vertical)
                ->constraints(...$constraints)
                ->widgets(
                    ListWidget::default()
                        ->startCorner(Corner::BottomLeft)
                        ->items(...$listItems),
                    BlockWidget::default()
                        ->borders(Borders::ALL)
                        ->borderType(BorderType::Rounded)
                        ->borderStyle($style)
                        ->widget(
                            ParagraphWidget::fromLines(Line::parse($line)),
                        ),
                );
    }
}

<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\Mouse;
use LaraTui\Component;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;

abstract class Pane extends Component
{
    protected ?Area $area = null;

    public function register(): void {}

    #[Mouse(true)]
    public function mouseMove(array $data): void
    {
        /** @var MouseEvent $event */
        $event = $data['event'];

        if ($event->kind !== MouseEventKind::Down) {
            return;
        }

        if (! $this->area) {
            return;
        }

        if ($event->row >= $this->area->top() && $event->row <= $this->area->bottom() && $event->column >= $this->area->left() && $event->column <= $this->area->right()) {
            $this->activate();

            return;
        }

        $this->deactivate();
    }
}

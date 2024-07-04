<?php

namespace LaraTui\Panes;

use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class OutputLog extends Pane
{
    public function render(): Widget
    {
        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->titles(Title::fromString('  Output log'))
                ->borderStyle($this->isActive ? Style::default()->red() : Style::default())
                ->widget(
                    ParagraphWidget::fromString($this->state->get('output_log', '')),
                );
    }
}

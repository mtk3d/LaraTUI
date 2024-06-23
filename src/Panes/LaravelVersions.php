<?php

namespace LaraTui\Panes;

use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class LaravelVersions extends Pane
{
    public function init(): void {}

    public function render(): Widget
    {
        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
                ->titles(
                    Title::fromString(' 󰫐 Project'),
                )
                ->titleStyle(Style::default()->white())
                ->widget(
                    ParagraphWidget::fromString($this->state->get('project_name')),
                );
    }
}

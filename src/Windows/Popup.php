<?php

namespace LaraTui\Windows;

use LaraTui\CommandAttributes\KeyPressed;
use PhpTui\Term\KeyCode;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\Extension\Core\Widget\Block\Padding;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Text;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class Popup extends Window
{
    private Display $display;

    public function init(Display $display): void
    {
        $this->display = $display;
        $this->eventBus
            ->listen('open_dialog', fn () => $this->showWindow());
    }

    #[KeyPressed(KeyCode::Esc)]
    public function hide(): void
    {
        $this->hideWindow();
    }

    public function render(): Widget
    {
        $lines = Text::parse($this->state->get('update_log', ''));
        $height = $lines->height();
        $p = ParagraphWidget::fromText($lines);
        $viewportHeight = $this->display->viewportArea()->height;
        $bottomLineOfPopup = $viewportHeight - 17;
        $p->scroll = [$height - $bottomLineOfPopup, 0];

        return BlockWidget::default()
            ->padding(Padding::fromScalars(12, 12, 8, 8))
            ->widget(
                BlockWidget::default()
                    ->borders(Borders::ALL)
                    ->borderType(BorderType::Rounded)
                    ->widget($p),
            );
    }
}

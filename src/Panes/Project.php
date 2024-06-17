<?php

namespace LaravelSailTui\Panes;

use LaravelSailTui\Panes\Pane;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class Project extends Pane
{
	public function welcomeMessage(): string
	{
		$message = <<<HEREDOC
   _                               _    _______ _    _ _____
  | |                             | |  |__   __| |  | |_   _|
  | |     __ _ _ __ __ ___   _____| |     | |  | |  | | | |
  | |    / _` | '__/ _` \ \ / / _ \ |     | |  | |  | | | |
  | |___| (_| | | | (_| |\ V /  __/ |     | |  | |__| |_| |_
  |______\__,_|_|  \__,_| \_/ \___|_|     |_|   \____/|_____|
HEREDOC;
		return $message;
	}

	public function render(): Widget
	{
		return
			BlockWidget::default()
			->borders(Borders::ALL)
			->borderType(BorderType::Rounded)
			->titles(Title::fromString(' 󰫐 Project'))
			->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
			->widget(
					CompositeWidget::fromWidgets(
						ParagraphWidget::fromString($this->welcomeMessage()),
					)
			);
	}
}

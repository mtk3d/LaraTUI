<?php

namespace LaraTui\Panes;

use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Extension\Core\Widget\Table\TableCell;
use PhpTui\Tui\Extension\Core\Widget\Table\TableRow;
use PhpTui\Tui\Extension\Core\Widget\TableWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class Project extends Pane
{
    public function welcomeMessage(): string
    {
        $message = <<<HEREDOC
  _                   _____ _   _ ___ 
 | |    __ _ _ __ __ |_   _| | | |_ _|
 | |   / _` | '__/ _` || | | | | || | 
 | |__| (_| | | | (_| || | | |_| || | 
 |_____\__,_|_|  \__,_||_|  \___/|___|
HEREDOC;

        return $message;
    }

    public function getVersions(): array
    {
        return [
            TableRow::fromCells(
                TableCell::fromString('Laravel Framework'),
                TableCell::fromLine(
                    Line::parse('<fg=green> 11.10.0</>')
                ),
                TableCell::fromString('Supported Latest')
            ),
            TableRow::fromCells(
                TableCell::fromString('Local PHP'),
                TableCell::fromLine(
                    Line::parse('<fg=yellow> 8.2.1</>')
                ),
                TableCell::fromLine(Line::parse('Supported <fg=darkGray>(newer version available)</>'))
            ),
            TableRow::fromCells(
                TableCell::fromString('Local Composer'),
                TableCell::fromLine(
                    Line::parse('<fg=red> 2.7.6</>')
                ),
                TableCell::fromLine(Line::parse('Outdated <fg=darkGray>(update required)</>'))
            ),
        ];
    }

    public function getNumberOfUpdated(): array
    {
        $outdatedPackages = $this->state->get('outdated_packages', []);
        $result = 'You have some outdated packages: <fg=darkGray>(press <options=bold>u</> to run update)</>'.PHP_EOL;
        $minor = '<fg=red>Minor/Patch updates:</> '.count(array_filter($outdatedPackages, fn ($package) => $package['latest-status'] === 'semver-safe-update')).PHP_EOL;
        $major = '<fg=yellow>Major updates:</> '.count(array_filter($outdatedPackages, fn ($package) => $package['latest-status'] === 'update-possible')).PHP_EOL;

        return [
            Line::parse($result),
            Line::parse($minor),
            Line::parse($major),
        ];
    }

    public function render(): Widget
    {
        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->titles(Title::fromString('  Condition '), Title::fromString('  Logs '), Title::fromString('  Artisan '), Title::fromString(' 󰯷 envs'))
                ->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
                ->widget(
                    GridWidget::default()
                        ->direction(Direction::Vertical)
                        ->constraints(
                            Constraint::min(6),
                            Constraint::min(4),
                            Constraint::min(4),
                            Constraint::min(2),
                            Constraint::min(6),
                        )
                        ->widgets(
                            ParagraphWidget::fromString($this->welcomeMessage())->style(Style::default()->red()),
                            TableWidget::default()
                                ->widths(
                                    Constraint::length(20),
                                    Constraint::length(11),
                                    Constraint::length(20),
                                )
                                ->rows(
                                    ...$this->getVersions(),
                                ),
                            ParagraphWidget::fromLines(...$this->getNumberOfUpdated()),
                            ParagraphWidget::fromLines(
                                Line::parse('You have migrations waiting for execution: <fg=yellow>2</> <fg=darkGray>(press <options=bold>m</> to migrate)'),
                            ),
                            ParagraphWidget::fromLines(
                                Line::parse('Is your app publically visible?: <fg=green>Yes (http://23.213.21.2/)</> <fg=darkGray>(press <options=bold>p</> to change that or <options=bold>o</> to open in browser)</>')

                            )
                        ),
                );
    }
}

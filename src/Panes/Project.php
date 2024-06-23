<?php

namespace LaraTui\Panes;

use LaraTui\Panes\Services\VersionsParser;
use LaraTui\State\InstalledPackages;
use LaraTui\State\LaravelVersions;
use LaraTui\State\PHPVersions;
use LaraTui\Traits\TabManager;
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
    use TabManager;

    private Line $version;

    public function init(): void
    {
        $this->version = Line::fromString('Loading...');

        $this->loop->addTimer(8, function () {
            $this->version = VersionsParser::parseVersions(
                $this->state->get('existing_php_version', ''),
                $this->state->get(InstalledPackages::class),
                $this->state->get(LaravelVersions::class),
                $this->state->get(PHPVersions::class),
            );
        });
    }

    public function tabs(): array
    {
        return [
            Line::fromString('  Condition '), Line::fromString('  Logs '), Line::fromString('  Artisan '), Line::fromString(' 󰯷 envs'),
        ];
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
                ->titles(...$this->titles())
                ->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
                ->widget(
                    GridWidget::default()
                        ->direction(Direction::Vertical)
                        ->constraints(
                            Constraint::min(6),
                            Constraint::min(3),
                            Constraint::min(4),
                            Constraint::min(4),
                            Constraint::min(2),
                            Constraint::min(6),
                        )
                        ->widgets(
                            ParagraphWidget::fromString($this->welcomeMessage())->style(Style::default()->red()),
                            ParagraphWidget::fromLines($this->version),
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

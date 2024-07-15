<?php

namespace LaraTui\PaneTabs;

use Illuminate\Support\Str;
use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\Commands\FetchVersionsInfoCommand;
use LaraTui\Commands\MigrationStatusCommand;
use LaraTui\Commands\UpdatePackagesCommand;
use LaraTui\Component;
use LaraTui\Panes\Services\VersionsParser;
use LaraTui\State\MigrationStatus;
use LaraTui\State\OutdatedPackages;
use LaraTui\State\VersionsInfo;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Layout;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;

class ProjectCondition extends Component
{
    private Widget $versions;

    public function init(): void
    {
        $this->versions = ParagraphWidget::fromSpans(Span::fromString('Loading...')->darkGray());

        $this->execute(new FetchVersionsInfoCommand());
        $this->execute(new MigrationStatusCommand());

        $this->eventBus->listen('BuildVersionsFinished', function () {
            $this->versions = VersionsParser::parseVersions(
                $this->state->get(VersionsInfo::class),
            );
        });
    }

    #[KeyPressed('u')]
    public function update(): void
    {
        $this->execute(new UpdatePackagesCommand());
        $this->emit('open_dialog');
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

    public function getNumberOfUpdated(): array
    {
        $outdatedPackages = collect($this->state
            ->get(OutdatedPackages::class)
            ?->installed)
            ->countBy(fn ($package) => Str::camel($package->latestStatus));

        $possibleToUpdate = $outdatedPackages->get('possibleToUpdate', 0);
        $safeToUpdate = $outdatedPackages->get('semverSafeUpdate', 0);

        return [
            Line::parse('<fg=darkGray>Press <options=bold><fg=blue>u</></> to run safe update, or <options=bold><fg=blue>U</></> to update all)</>'),
            Line::parse("<fg=red>Packages major updates:</> $possibleToUpdate"),
            Line::parse("<fg=yellow>Packages minor/patch updates:</> $safeToUpdate"),
        ];
    }

    public function getMigrationStatus(): Widget
    {
        $migrationStatus = $this->state->get(MigrationStatus::class);

        if (! $migrationStatus) {
            return ParagraphWidget::fromSpans(Span::fromString('Loading...')->gray());
        }

        if ($migrationStatus->pending) {
            $line = Line::parse("<fg=yellow>$migrationStatus->pending/$migrationStatus->all</> migrations are waiting for execution <fg=darkGray>(press <options=bold><fg=blue>m</></> to migrate)");
        } else {
            $line = Line::parse('You don\'t have any pending migrations <fg=green></>');
        }

        return ParagraphWidget::fromLines($line);
    }

    public function render(Area $area): Widget
    {
        $constraints = [
            Constraint::min(6),
            Constraint::min(4),
            Constraint::min(4),
            Constraint::min(2),
            Constraint::min(6),
        ];
        $layout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints($constraints);

        return
            GridWidget::default()
                ->direction(Direction::Vertical)
                ->constraints(...$constraints)
                ->widgets(
                    ParagraphWidget::fromString($this->welcomeMessage())->style(Style::default()->red()),
                    $this->versions,
                    ParagraphWidget::fromLines(...$this->getNumberOfUpdated()),
                    $this->getMigrationStatus(),
                    ParagraphWidget::fromLines(
                        Line::parse('Is your app publically visible?: <fg=green>Yes (http://23.213.21.2/)</> <fg=darkGray>(press <options=bold>p</> to change that or <options=bold>o</> to open in browser)</>')

                    )
                );
    }
}

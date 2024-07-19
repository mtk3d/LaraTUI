<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\Mouse;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\OutdatedPackagesCommand;
use LaraTui\State\OutdatedPackages as OutdatedPackagesState;
use LaraTui\Traits\ListManager;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\List\ListItem;
use PhpTui\Tui\Extension\Core\Widget\List\ListState;
use PhpTui\Tui\Extension\Core\Widget\ListWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Text;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class OutdatedPackages extends Pane
{
    use ListManager;

    private array $packages = [];

    private bool $isLoading;

    private int $d = 0;

    public function init(): void
    {
        $this->isLoading = true;
        $this->collectServicesData();
    }

    protected function items(): array
    {
        return $this->packages;
    }

    #[Periodic(30)]
    public function collectServicesData(): void
    {
        $this->execute(new OutdatedPackagesCommand())
            ->then(fn () => $this->isLoading = false);
    }

    private function updateListOfPackages(): void
    {
        /** @var OutdatedPackagesState $outdatedPackages * */
        $outdatedPackages = $this->state->get(OutdatedPackagesState::class, null);

        if ($outdatedPackages) {
            $this->packages = array_map(function ($package) {
                $name = $package->name;
                $version = $package->version;
                $latest = $package->latest;
                $prefix = match ($package->latestStatus) {
                    'update-possible' => '<fg=yellow></>',
                    'semver-safe-update' => '<fg=red></>',
                };

                return ListItem::new(
                    Text::parse("$prefix $name $version -> $latest"),
                );
            }, $outdatedPackages->installed);
        }
    }

    #[Mouse()]
    public function click(array $data): void
    {
        if (!isset($this->area)) {
            return;
        }

        /** @var MouseEvent $event */
        $event = $data['event'];

        if ($event->kind !== MouseEventKind::Down) {
            return;
        }

        $this->selectedItem = $event->row - $this->area->top() - 1;
    }

    public function render(Area $area): Widget
    {
        $this->area = $area;
        $this->updateListOfPackages();

        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->borderStyle($this->isActive ? Style::default()->red() : Style::default())
                ->titles(
                    Title::fromString('  Package updates'),
                )
                ->titleStyle(Style::default()->white())
                ->widget(
                    $this->isLoading ?
                    ParagraphWidget::fromLines(
                        Line::parse('<fg=darkGray>Loading...</>')
                    ) : (
                        empty($this->packages) ?
                        ParagraphWidget::fromLines(
                            Line::parse('All your packages are up to date')
                        ) :
                            ListWidget::default()
                                ->highlightSymbol('')
                                ->highlightStyle(Style::default()->lightRed())
                                ->state(new ListState(0, $this->isActive ? $this->selectedItem : null))
                                ->items(
                                    ...$this->packages,
                                )
                    )
                );
    }
}

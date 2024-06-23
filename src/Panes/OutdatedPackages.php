<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\OutdatedPackagesCommand;
use LaraTui\Traits\ListManager;
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

    public function init(): void
    {
        $this->collectServicesData();
    }

    protected function items(): array
    {
        return $this->packages;
    }

    #[Periodic(30)]
    public function collectServicesData(): void
    {
        $this->commandBus->dispatch(OutdatedPackagesCommand::class);
    }

    private function updateListOfPackages(): void
    {
        $outdatedPackages = $this->state->get('outdated_packages', []);

        $this->packages = array_map(function ($package) {
            $name = $package['name'];
            $version = $package['version'];
            $latest = $package['latest'];
            $lastestStatus = $package['latest-status'];
            $prefix = match ($lastestStatus) {
                'update-possible' => '<fg=yellow></>',
                'semver-safe-update' => '<fg=red></>',
            };

            return ListItem::new(
                Text::parse("$prefix $name $version -> $latest"),
            );
        }, $outdatedPackages);
    }

    public function render(): Widget
    {
        $this->updateListOfPackages();

        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
                ->titles(
                    Title::fromString('  Package updates'),
                )
                ->titleStyle(Style::default()->white())
                ->widget(
                    empty($this->packages) ?
                    ParagraphWidget::fromLines(Line::parse('<fg=darkGray>Loading...</>')) :
                    ListWidget::default()
                        ->highlightSymbol('')
                        ->highlightStyle(Style::default()->lightRed())
                        ->state(new ListState(0, $this->isSelected ? $this->selectedItem : null))
                        ->items(
                            ...$this->packages,
                        )
                );
    }
}

<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\OutdatedPackagesCommand;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\List\ListItem;
use PhpTui\Tui\Extension\Core\Widget\List\ListState;
use PhpTui\Tui\Extension\Core\Widget\ListWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Text;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class OutdatedPackages extends Pane
{
    private int $selectedItem = 0;

    private int $maxItems = 0;

    public function init(): void
    {
        $this->collectServicesData();
    }

    #[KeyPressed('k')]
    public function up(): void
    {
        if ($this->selectedItem > 0) {
            $this->selectedItem--;
        }
    }

    #[KeyPressed('j')]
    public function down(): void
    {
        if ($this->selectedItem < $this->maxItems) {
            $this->selectedItem++;
        }
    }

    #[Periodic(30)]
    public function collectServicesData(): void
    {
        $this->commandBus->dispatch(OutdatedPackagesCommand::$commandName);
    }

    private function getListOfPackages(): array
    {
        $outdatedPackages = $this->state->get('outdated_packages', []);

        return array_map(function ($package) {
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
        $outdatedPackages = $this->getListOfPackages();
        $this->maxItems = count($outdatedPackages) - 1;

        return
            BlockWidget::default()
                ->borders(Borders::ALL)
                ->borderType(BorderType::Rounded)
                ->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
                ->titles(
                    Title::fromString('  Package updates'),
                )
                ->titleStyle(Style::default()->bold())
                ->widget(
                    ListWidget::default()
                        ->highlightSymbol('')
                        ->highlightStyle(Style::default()->lightRed())
                        ->state(new ListState(0, $this->isSelected ? $this->selectedItem : null))
                        ->items(
                            ...$outdatedPackages,
                        )
                );
    }
}

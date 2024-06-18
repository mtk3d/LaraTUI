<?php

namespace LaraTui\Panes;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\ServicesStatusCommand;
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

class Services extends Pane
{
	private int $selectedItem = 0;
	private array $services = [
		[
			'icon' => '',
			'name' => 'MySQL',
			'service' => 'mysql',
		],
		[
			'icon' => '',
			'name' => 'PostgreSQL',
			'service' => 'pgsql',
		],
		[
			'icon' => '',
			'name' => 'MariaDB',
			'service' => 'mariadb',
		],
		[
			'icon' => '',
			'name' => 'Redis',
			'service' => 'redis',
		],
		[
			'icon' => '',
			'name' => 'Memcached',
			'service' => 'memcached',
		],
		[
			'icon' => '',
			'name' => 'Meilisearch',
			'service' => 'meilisearch',
		],
		[
			'icon' => '',
			'name' => 'Typesense',
			'service' => 'typesense',
		],
		[
			'icon' => '',
			'name' => 'Minio',
			'service' => 'minio',
		],
		[
			'icon' => '',
			'name' => 'Mailpit',
			'service' => 'mailpit',
		],
	];

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
		if ($this->selectedItem < count($this->services) - 1) {
			$this->selectedItem++;
		}
	}

	#[Periodic(1)]
	public function collectServicesData(): void
	{
		$this->commandBus->dispatch(ServicesStatusCommand::$commandName);
	}

	private function combineServicesWithStatus(): array
	{
		$servicesStatus = $this->state->get('services_status');
		$services = array_map(
			function ($service) use ($servicesStatus) {
				$container = $service['service'];
				$service['status'] = 'disabled';

				if (isset($servicesStatus["$container"])) {
					$service['status'] = $servicesStatus["$container"];
				}

				return $service;
			},
			$this->services
		);

		$enabled = array_filter($services, fn ($service) => $service['status'] !== 'disabled');
		$disabled = array_filter($services, fn ($service) => $service['status'] === 'disabled');

		return [...$enabled, ...$disabled];
	}

	public function render(): Widget
	{
		$services = array_map(
			function ($service) {
				$icon = $service['icon'];
				$name = $service['name'];
				$status = $service['status'];
				$style = Style::default()->darkGray();

				if ($status !== 'disabled') {
					$style = Style::default();
				}

				return ListItem::new(
					Text::fromString("$icon $name ($status)")->patchStyle($style),
				);
			},
			$this->combineServicesWithStatus()
		);

		return
			BlockWidget::default()
			->borders(Borders::ALL)
			->borderType(BorderType::Rounded)
			->borderStyle($this->isSelected ? Style::default()->red() : Style::default())
			->titles(
				Title::fromString(' 󰡨 Services'),
			)
			->titleStyle(Style::default()->bold())
			->widget(
				ListWidget::default()
					->highlightSymbol('')
					->highlightStyle(Style::default()->lightRed())
					->state(new ListState(0, $this->isSelected ? $this->selectedItem : null))
					->items(
						...$services,
					)
			);
	}
}

<?php

namespace LaravelSailTui\Panes;

use LaravelSailTui\CommandAttributes\KeyPressed;
use LaravelSailTui\CommandAttributes\Periodic;
use LaravelSailTui\CommandBus;
use LaravelSailTui\EventBus;
use LaravelSailTui\State;
use React\EventLoop\LoopInterface;
use ReflectionObject;

abstract class Pane
{
	protected bool $isSelected = false;
	protected EventBus $eventBus;
	protected CommandBus $commandBus;
	protected State $state;
	protected array $timers = [];

	public function __construct()
	{
	}

	protected function init(): void
	{}

	public function selectPane(): void
	{
		$this->isSelected = true;
	}

	public function deselectPane(): void
	{
		$this->isSelected = false;
	}

	public function registerPane(
		LoopInterface $loop,
		EventBus $eventBus,
		CommandBus $commandBus,
		State $state,
	): void
	{
		$reflection = new ReflectionObject($this);
		$methods = $reflection->getMethods();

		foreach ($methods as $method) {
			$attributes = $method->getAttributes(KeyPressed::class);
			foreach ($attributes as $attribute) {
				$methodName = $method->getName();
				$attribute = $attribute->newInstance();
				$eventBus->listenTo(
					$attribute->key,
					function () use ($attribute, $methodName) {
						if ($this->isSelected || $attribute->global) {
							$this->$methodName();
						}
					}
				);
			}

			$attributes = $method->getAttributes(Periodic::class);
			foreach ($attributes as $attribute) {
				$methodName = $method->getName();
				$attribute = $attribute->newInstance();
				$this->timers[] = $loop->addPeriodicTimer($attribute->interval, [$this, $methodName]);
			}
		}

		$this->eventBus = $eventBus;
		$this->commandBus = $commandBus;
		$this->state = $state;

		$this->init();
	}

	protected function emit(string $event, array $data): void
	{
		$this->eventBus->emit($event, $data);
	}

	public function unmount(): void
	{
		foreach($this->timers as $timer) {
			$timer->stop();
		}
	}
}

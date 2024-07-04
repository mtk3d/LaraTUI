<?php

namespace LaraTui\Windows;

use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\CommandInvoker;
use LaraTui\Commands\Command;
use LaraTui\EventBus;
use LaraTui\State;
use PhpTui\Tui\Widget\Widget;
use React\EventLoop\LoopInterface;
use ReflectionObject;

abstract class Window
{
    public bool $isHidden = true;

    protected EventBus $eventBus;

    protected CommandInvoker $commandInvoker;

    protected LoopInterface $loop;

    protected State $state;

    protected array $timers = [];

    protected array $panes = [];

    protected array $panesInstances = [];

    public function __construct() {}

    protected function init(): void {}

    public function showWindow(): void
    {
        $this->isHidden = false;
    }

    public function hideWindow(): void
    {
        $this->isHidden = true;
    }

    abstract public function render(): Widget;

    public function registerWindow(
        LoopInterface $loop,
        EventBus $eventBus,
        State $state,
        CommandInvoker $commandInvoker,
    ): void {
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
                        if (! $this->isHidden || $attribute->global) {
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
        $this->state = $state;
        $this->loop = $loop;
        $this->commandInvoker = $commandInvoker;

        $this->registerPanes();

        $this->init();
    }

    protected function registerPanes(): void
    {
        foreach ($this->panes as $paneClass) {
            $this->panesInstances[$paneClass] = new $paneClass();
            $this->panesInstances[$paneClass]->registerPane($this->loop, $this->eventBus, $this->state, $this->commandInvoker);
        }
    }

    protected function renderPane(string $paneClass): Widget
    {
        if (! isset($this->panesInstances[$paneClass])) {
            throw new \Exception();
        }

        return $this->panesInstances[$paneClass]->render();
    }

    protected function emit(string $event, array $data): void
    {
        $this->eventBus->emit($event, $data);
    }

    protected function execute(Command $command): void
    {
        $this->commandInvoker->invoke($command);
    }

    public function unmount(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }
}

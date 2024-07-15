<?php

namespace LaraTui;

use DI\Container;
use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Mouse;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\Command;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Widget\Widget;
use React\EventLoop\LoopInterface;
use ReflectionObject;

abstract class Component
{
    protected array $components = [];

    protected array $componentInstances = [];

    protected bool $isActive = false;

    protected array $timers = [];

    public function __construct(
        protected readonly LoopInterface $loop,
        protected readonly EventBus $eventBus,
        protected readonly State $state,
        protected readonly CommandInvoker $commandInvoker,
        protected readonly Container $container,
    ) {
        if (method_exists($this, 'init')) {
            $this->container->call([$this, 'init']);
        }

        $reflection = new ReflectionObject($this);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $attributes = [
                ...$method->getAttributes(KeyPressed::class),
                ...$method->getAttributes(Mouse::class),
            ];

            foreach ($attributes as $attribute) {
                $methodName = $method->getName();
                $attribute = $attribute->newInstance();
                $eventBus->listen(
                    $attribute->key,
                    function (array $data) use ($attribute, $methodName) {
                        if ($this->isActive || $attribute->global) {
                            $this->$methodName($data);
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

        $this->registerComponents();

        if (method_exists($this, 'mount')) {
            $this->container->call([$this, 'mount']);
        }
    }

    private function registerComponents(): void
    {
        foreach ($this->components as $component) {
            $this->componentInstances[$component] = $this->container
                ->make($component);
        }
    }

    protected function renderComponent(string $component, Area $area): Widget
    {
        return $this->getComponent($component)->render($area);
    }

    protected function getComponent(string $component)
    {
        return $this->componentInstances[$component];
    }

    protected function execute(Command $command): mixed
    {
        return $this->commandInvoker->invoke($command);
    }

    protected function emit(string $event, array $data = []): void
    {
        $this->eventBus->emit($event, $data);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    abstract public function render(Area $area): Widget;
}

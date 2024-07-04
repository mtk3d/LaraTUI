<?php

namespace LaraTui;

use DI\Container;
use LaraTui\CommandAttributes\KeyPressed;
use LaraTui\CommandAttributes\Periodic;
use LaraTui\Commands\Command;
use React\EventLoop\LoopInterface;
use ReflectionObject;

abstract class Component
{
    protected $isActive = false;

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
            $attributes = $method->getAttributes(KeyPressed::class);
            foreach ($attributes as $attribute) {
                $methodName = $method->getName();
                $attribute = $attribute->newInstance();
                $eventBus->listen(
                    $attribute->key,
                    function () use ($attribute, $methodName) {
                        if (! $this->isActive || $attribute->global) {
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

        $this->register();

        if (method_exists($this, 'mount')) {
            $this->container->call([$this, 'mount']);
        }
    }

    public abstract function register(): void;

    protected function execute(Command $command): void
    {
        $this->commandInvoker->invoke($command);
    }
}

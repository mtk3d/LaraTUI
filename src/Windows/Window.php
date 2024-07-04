<?php

namespace LaraTui\Windows;

use LaraTui\Component;
use PhpTui\Tui\Widget\Widget;

abstract class Window extends Component
{
    protected array $panes = [];

    protected array $panesInstances = [];

    public function showWindow(): void
    {
        $this->isActive = true;
    }

    public function hideWindow(): void
    {
        $this->isActive = false;
    }

    abstract public function render(): Widget;

    public function register(): void
    {
        foreach ($this->panes as $paneClass) {
            $this->panesInstances[$paneClass] = $this->container
                ->make($paneClass);
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


    public function unmount(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }
}

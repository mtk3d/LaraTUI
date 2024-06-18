<?php

namespace LaraTui;

class EventBus
{
    private $register = [];

    public function listenTo(string $eventName, callable $func): void
    {
        if (! isset($this->register[$eventName])) {
            $this->register[$eventName] = [];
        }

        $this->register[$eventName][] = $func;
    }

    public function emit(string $eventName, array $data = []): void
    {
        if (! isset($this->register[$eventName])) {
            return;
        }

        foreach ($this->register[$eventName] as $func) {
            $func($data);
        }
    }
}

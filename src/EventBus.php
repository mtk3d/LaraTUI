<?php

namespace LaraTui;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\FunctionKeyEvent;
use PhpTui\Term\KeyCode;

class EventBus
{
    private $register = [];

    public function listenTo(
        KeyCode|string|int $event,
        callable $func
    ): void {
        if ($event instanceof KeyCode) {
            $event = $event->name;
        }

        if (! isset($this->register[$event])) {
            $this->register[$event] = [];
        }

        $this->register[$event][] = $func;
    }

    public function emit(
        CharKeyEvent|CodedKeyEvent|FunctionKeyEvent $event,
        array $data = []
    ): void {
        $key = null;

        if ($event instanceof CharKeyEvent) {
            $key = $event->char;
        }

        if ($event instanceof CodedKeyEvent) {
            $key = $event->code->name;
        }

        if ($event instanceof FunctionKeyEvent) {
            $key = "F{$event->number}";
        }

        if (! isset($this->register[$key])) {
            return;
        }

        foreach ($this->register[$key] as $func) {
            $func($data);
        }
    }
}

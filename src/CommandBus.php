<?php

namespace LaraTui;

class CommandBus
{
    private $register = [];

    public function reactTo(string $commandClass, callable $func): void
    {
        if (! isset($this->register[$commandClass])) {
            $this->register[$commandClass] = [];
        }

        $this->register[$commandClass][] = $func;
    }

    public function dispatch(string $commandClass, array $data = []): void
    {
        if (! isset($this->register[$commandClass])) {
            return;
        }

        foreach ($this->register[$commandClass] as $func) {
            $func($data);
        }
    }
}

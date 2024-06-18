<?php

namespace LaraTui;

class CommandBus
{
    private $register = [];

    public function reactTo(string $commandName, callable $func): void
    {
        if (!isset($this->register[$commandName])) {
            $this->register[$commandName] = [];
        }

        $this->register[$commandName][] = $func;
    }

    public function dispatch(string $commandName, array $data = []): void
    {
        if (!isset($this->register[$commandName])) {
            return;
        }

        foreach ($this->register[$commandName] as $func) {
            $func($data);
        }
    }
}

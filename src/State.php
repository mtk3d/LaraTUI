<?php

namespace LaravelSailTui;

class State
{
    private array $state = [];

    public function set(string $key, mixed $value): void
    {
        $this->state[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->state[$key])) {
            return $this->state[$key];
        }

        return $default;
    }

    public function delete(string $key): void
    {
        if (isset($this->state[$key])) {
            unset($this->state[$key]);
        }
    }

    public function append(string $key, mixed $value): void
    {
        if (!isset($this->state[$key])) {
            $this->state[$key] = $value;
            return;
        }

        if (is_string($this->state[$key])) {
            $this->state[$key] .= $value;
            return;
        }

        if (is_array($this->state[$key])) {
            $this->state[$key][] = $value;
            return;
        }
    }
}

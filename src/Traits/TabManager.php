<?php

namespace LaraTui\Traits;

use LaraTui\CommandAttributes\KeyPressed;

trait TabManager
{
    protected int $currentTab = 0;

    abstract public function tabs(): array;

    abstract public function setTab(int $tab): void;

    protected function tabsCount(): int
    {
        return count($this->tabs());
    }

    #[KeyPressed(']')]
    public function nextTab(): void
    {
        if ($this->currentTab < $this->tabsCount() - 1) {
            $this->currentTab++;
        }

        $this->setTab($this->currentTab);
    }

    #[KeyPressed('[')]
    public function previousTab(): void
    {
        if ($this->currentTab > 0) {
            $this->currentTab--;
        }

        $this->setTab($this->currentTab);
    }
}

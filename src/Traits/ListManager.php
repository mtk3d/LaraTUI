<?php

namespace LaraTui\Traits;

use LaraTui\CommandAttributes\KeyPressed;

trait ListManager
{
    protected int $selectedItem = 0;

    abstract protected function items(): array;

    protected function itemsCount(): int
    {
        return count($this->items());
    }

    #[KeyPressed('k')]
    public function up(): void
    {
        if ($this->selectedItem > 0) {
            $this->selectedItem--;
        }
    }

    #[KeyPressed('j')]
    public function down(): void
    {
        if ($this->selectedItem < $this->itemsCount() - 1) {
            $this->selectedItem++;
        }
    }
}

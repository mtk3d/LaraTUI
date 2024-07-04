<?php

namespace LaraTui\Panes;

use LaraTui\Component;
use PhpTui\Tui\Widget\Widget;

abstract class Pane extends Component
{
    public function selectPane(): void
    {
        $this->isActive = true;
    }

    public function deselectPane(): void
    {
        $this->isActive = false;
    }

    abstract public function render(): Widget;

    public function register(): void {}
}

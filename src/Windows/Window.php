<?php

namespace LaraTui\Windows;

use LaraTui\Component;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Widget\Widget;

abstract class Window extends Component
{
    public function unmount(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }
}

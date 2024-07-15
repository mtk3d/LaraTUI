<?php

namespace LaraTui\Windows;

use LaraTui\Component;

abstract class Window extends Component
{
    public function unmount(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }
}

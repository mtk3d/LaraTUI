<?php

use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\DisplayBuilder;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

return [
    LoopInterface::class => fn () => Loop::get(),
    Terminal::class => fn () => Terminal::new(),
    Display::class => fn () => DisplayBuilder::default()->build(),
];

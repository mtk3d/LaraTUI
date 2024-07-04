<?php

namespace LaraTui;

use Invoker\InvokerInterface;
use LaraTui\Commands\Command;

class CommandInvoker
{
    public function __construct(
        private readonly InvokerInterface $invoker,
    ) {}

    public function invoke(Command $command): void
    {
        $this->invoker->call($command);
    }
}

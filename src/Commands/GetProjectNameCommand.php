<?php

namespace LaraTui\Commands;

class GetProjectNameCommand extends Command
{
    public function init(): void
    {
        $this->state->set('project_name', 'Loading...');
    }

    public function execute(array $data): void
    {
        $this->state->set('project_name', basename(getcwd()));
    }
}

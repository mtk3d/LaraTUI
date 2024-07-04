<?php

namespace LaraTui\Commands;

use LaraTui\State;

class GetProjectNameCommand extends Command
{
    public function __invoke(State $state): void
    {
        $state->set('project_name', 'Loading...');
        $state->set('project_name', basename(getcwd()));
    }
}

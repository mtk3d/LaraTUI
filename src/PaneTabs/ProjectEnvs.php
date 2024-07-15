<?php

namespace LaraTui\PaneTabs;

use LaraTui\Component;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Widget\Widget;

class ProjectEnvs extends Component
{
    public function render(Area $area): Widget
    {
        return ParagraphWidget::fromLines(
            Line::fromString('Project envs'),
        );
    }
}

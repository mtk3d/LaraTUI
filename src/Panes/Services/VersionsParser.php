<?php

namespace LaraTui\Panes\Services;

use LaraTui\State\VersionInfoLine;
use LaraTui\State\VersionsInfo;
use PhpTui\Tui\Extension\Core\Widget\Table\TableCell;
use PhpTui\Tui\Extension\Core\Widget\Table\TableRow;
use PhpTui\Tui\Extension\Core\Widget\TableWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;

class VersionsParser
{
    public static function parseVersions(
        VersionsInfo $versionsInfo,
    ) {
        $rows = collect($versionsInfo->lines)->map(function (VersionInfoLine $versionInfo) {
            $iconSpan = match (true) {
                $versionInfo->isSupported && $versionInfo->isLatest => Span::fromString('')->green(),
                $versionInfo->isSupported => Span::fromString('')->yellow(),
                default => Span::fromString('')->red(),
            };

            $versionSpan = Span::fromString(" $versionInfo->version ");
            $supportedSpan = Span::fromString($versionInfo->isSupported ? 'Supported' : 'Outdated');
            $updateInfoSpan = Span::fromString(" $versionInfo->updateInfo")->darkGray();

            return TableRow::fromCells(
                TableCell::fromString($versionInfo->name),
                TableCell::fromLine(
                    Line::fromSpans($iconSpan, $versionSpan),
                ),
                TableCell::fromLine(
                    Line::fromSpans($supportedSpan, $updateInfoSpan),
                ),
            );
        })->toArray();

        return TableWidget::default()
            ->widths(
                Constraint::length(10),
                Constraint::length(11),
                Constraint::length(20),
            )
            ->rows(
                ...$rows,
            );
    }
}

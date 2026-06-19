<?php

namespace App\Filament\Widgets;

use App\Support\AccessControl;
use App\Support\OwnerBranchSummary;
use Filament\Widgets\Widget;

class OwnerBranchSummaryWidget extends Widget
{
    protected static string $view = 'filament.widgets.owner-branch-summary';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isOwner();
    }

    public function getRows(): array
    {
        return OwnerBranchSummary::rows()->all();
    }

    public function getPeriodLabel(): string
    {
        return now()->translatedFormat('F Y');
    }
}

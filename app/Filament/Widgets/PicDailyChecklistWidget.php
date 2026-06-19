<?php

namespace App\Filament\Widgets;

use App\Support\AccessControl;
use App\Support\PicDailyChecklist;
use Filament\Widgets\Widget;

class PicDailyChecklistWidget extends Widget
{
    protected static string $view = 'filament.widgets.pic-daily-checklist';

    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isPic() && AccessControl::userBranchId() !== null;
    }

    /**
     * @return array<int, array{label: string, done: bool, url: ?string, hint: string, tone: string}>
     */
    public function getItems(): array
    {
        return PicDailyChecklist::items();
    }

    public function getCompletedCount(): int
    {
        return PicDailyChecklist::completedCount();
    }

    public function getTotalCount(): int
    {
        return PicDailyChecklist::totalCount();
    }

    public function getRequiredRemaining(): int
    {
        return PicDailyChecklist::requiredRemaining();
    }

    public function getGreeting(): string
    {
        return PicDailyChecklist::greeting();
    }

    public function getTodayLabel(): string
    {
        return PicDailyChecklist::todayLabel();
    }

    public function getBranchName(): string
    {
        return auth()->user()->branch?->name ?? '-';
    }
}

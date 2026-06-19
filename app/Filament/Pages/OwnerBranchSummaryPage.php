<?php

namespace App\Filament\Pages;

use App\Support\AccessControl;
use App\Support\OwnerBranchSummary as OwnerBranchSummaryData;
use Filament\Pages\Page;

class OwnerBranchSummaryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Ringkasan Cabang';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.owner-branch-summary';

    public ?int $month = null;

    public ?int $year = null;

    public static function canAccess(): bool
    {
        return AccessControl::isOwner();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return AccessControl::isOwner();
    }

    public function getTitle(): string
    {
        return 'Ringkasan per Cabang';
    }

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function getRows(): array
    {
        return OwnerBranchSummaryData::rows($this->month, $this->year)->all();
    }

    public function getPeriodLabel(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
    }
}

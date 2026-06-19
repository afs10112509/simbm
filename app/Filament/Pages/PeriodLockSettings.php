<?php

namespace App\Filament\Pages;

use App\Services\PeriodLockService;
use App\Support\AccessControl;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PeriodLockSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationLabel = 'Kunci Periode';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.period-lock-settings';

    public ?int $lockMonth = null;

    public ?int $lockYear = null;

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
        return 'Kunci Periode';
    }

    public function mount(): void
    {
        $this->lockMonth = now()->subMonth()->month;
        $this->lockYear = now()->subMonth()->year;
    }

    public function getLockedMonths(): array
    {
        return PeriodLockService::lockedMonths()->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lock_period')
                ->label('Kunci Periode')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('PIC tidak bisa mengubah data pada periode ini ke belakang.')
                ->action(function (): void {
                    PeriodLockService::lockMonth(
                        (int) $this->lockYear,
                        (int) $this->lockMonth,
                        auth()->id(),
                    );

                    Notification::make()
                        ->title('Periode dikunci')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function unlock(int $year, int $month): void
    {
        PeriodLockService::unlockMonth($year, $month);

        Notification::make()
            ->title('Kunci periode dibuka')
            ->success()
            ->send();
    }

    public function getSelectedPeriodLabel(): string
    {
        return Carbon::create($this->lockYear, $this->lockMonth, 1)->translatedFormat('F Y');
    }
}

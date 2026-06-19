<?php

namespace App\Support;

use App\Filament\Pages\BrilinkSaldoInput;
use App\Filament\Pages\BulkTransaction;
use App\Filament\Pages\ServiceInput;
use App\Filament\Pages\UpahKerjaInput;
use App\Models\BrilinkDailySnapshot;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;

class PicDailyChecklist
{
    /**
     * @return array<int, array{label: string, done: bool, url: ?string, hint: string, tone: string}>
     */
    public static function items(): array
    {
        if (! AccessControl::isPic() || AccessControl::userBranchId() === null) {
            return [];
        }

        $branchId = AccessControl::userBranchId();
        $today = now()->toDateString();
        $items = [];

        if (AccessControl::isKonterPic()) {
            $brilinkDone = BrilinkDailySnapshot::query()
                ->where('branch_id', $branchId)
                ->whereDate('snapshot_date', $today)
                ->exists();

            $items[] = [
                'label' => 'Input Saldo Brilink',
                'done' => $brilinkDone,
                'url' => BrilinkSaldoInput::getUrl(),
                'hint' => $brilinkDone ? 'Sudah diinput hari ini' : 'Belum diinput hari ini',
                'tone' => $brilinkDone ? 'success' : 'warning',
            ];
        }

        if (AccessControl::picHasBranch()) {
            $kasDone = Transaction::query()
                ->forLedgerReport()
                ->where('branch_id', $branchId)
                ->whereHas(
                    'account',
                    fn ($query) => $query->forPurpose(AccountPurpose::GENERAL)
                )
                ->whereDate('transaction_date', $today)
                ->exists();

            $items[] = [
                'label' => 'Input Kas Harian',
                'done' => $kasDone,
                'url' => BulkTransaction::getUrl(),
                'hint' => $kasDone ? 'Ada transaksi kas hari ini' : 'Belum ada transaksi kas hari ini',
                'tone' => $kasDone ? 'success' : 'warning',
            ];
        }

        if (AccessControl::isKonterPic()) {
            $serviceDone = Transaction::query()
                ->where('branch_id', $branchId)
                ->whereHas(
                    'account',
                    fn ($query) => $query->forPurpose(AccountPurpose::SERVICE)
                )
                ->whereDate('transaction_date', $today)
                ->exists();

            $items[] = [
                'label' => 'Input Service',
                'done' => $serviceDone,
                'url' => ServiceInput::getUrl(),
                'hint' => $serviceDone ? 'Ada service hari ini' : 'Opsional jika tidak ada service',
                'tone' => $serviceDone ? 'success' : 'gray',
            ];
        }

        if (AccessControl::isBengkelPic()) {
            $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

            $upahDone = $categoryId
                ? Transaction::query()
                    ->where('branch_id', $branchId)
                    ->where('transaction_category_id', $categoryId)
                    ->whereDate('transaction_date', $today)
                    ->exists()
                : false;

            $items[] = [
                'label' => 'Input Upah Kerja',
                'done' => $upahDone,
                'url' => UpahKerjaInput::getUrl(),
                'hint' => $upahDone ? 'Ada upah kerja hari ini' : 'Opsional jika tidak ada pekerjaan',
                'tone' => $upahDone ? 'success' : 'gray',
            ];
        }

        return $items;
    }

    public static function completedCount(): int
    {
        return collect(self::items())->where('done', true)->count();
    }

    public static function totalCount(): int
    {
        return count(self::items());
    }

    public static function requiredRemaining(): int
    {
        return collect(self::items())
            ->filter(fn (array $item) => $item['tone'] === 'warning' && ! $item['done'])
            ->count();
    }

    public static function greeting(): string
    {
        $hour = (int) now()->format('H');

        if ($hour < 11) {
            return 'Selamat pagi';
        }

        if ($hour < 15) {
            return 'Selamat siang';
        }

        if ($hour < 18) {
            return 'Selamat sore';
        }

        return 'Selamat malam';
    }

    public static function todayLabel(): string
    {
        return Carbon::now()->translatedFormat('l, d F Y');
    }
}

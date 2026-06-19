<div class="fi-wi-widget overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-white/10">
        <div>
            <div class="text-lg font-semibold text-gray-950 dark:text-white">Ringkasan per Cabang</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Performa bulan {{ $this->getPeriodLabel() }}</div>
        </div>
        <a
            href="{{ \App\Filament\Pages\OwnerBranchSummaryPage::getUrl() }}"
            class="text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400"
        >
            Lihat detail
        </a>
    </div>

    <div class="overflow-x-auto simbm-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left dark:bg-gray-950">
                <tr>
                    <th class="px-3 py-2 font-medium">Cabang</th>
                    <th class="px-3 py-2 font-medium">Kas (saldo)</th>
                    <th class="px-3 py-2 font-medium">Kas (laba bln)</th>
                    <th class="px-3 py-2 font-medium">Brilink</th>
                    <th class="px-3 py-2 font-medium">Service</th>
                    <th class="px-3 py-2 font-medium">Upah Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getRows() as $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-950 dark:text-white">{{ $row['branch']->name }}</div>
                            <div class="text-xs text-gray-500">{{ $row['type_label'] }}</div>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">Rp {{ number_format($row['kas_saldo'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">Rp {{ number_format($row['kas_laba_bulan'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($row['brilink_untung_bulan'] !== null)
                                Rp {{ number_format($row['brilink_untung_bulan'], 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($row['service_laba_bulan'] !== null)
                                Rp {{ number_format($row['service_laba_bulan'], 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($row['upah_kerja_bulan'] !== null)
                                Rp {{ number_format($row['upah_kerja_bulan'], 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

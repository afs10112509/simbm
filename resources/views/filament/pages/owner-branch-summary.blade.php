<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-gray-300">Bulan</label>
            <select wire:model.live="month" class="w-full mt-1 rounded-lg border-gray-600 dark:bg-gray-800">
                @foreach (range(1, 12) as $monthOption)
                    <option value="{{ $monthOption }}">
                        {{ \Carbon\Carbon::create(null, $monthOption, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-300">Tahun</label>
            <select wire:model.live="year" class="w-full mt-1 rounded-lg border-gray-600 dark:bg-gray-800">
                @foreach (range(now()->year - 2, now()->year) as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-4 text-sm text-gray-400">
        Periode: <strong class="text-gray-200">{{ $this->getPeriodLabel() }}</strong>
    </div>

    <div class="md:hidden">
        @foreach ($this->getRows() as $row)
            <div class="simbm-mobile-card">
                <div class="simbm-mobile-card__title">{{ $row['branch']->name }} ({{ $row['type_label'] }})</div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Kas saldo</span>
                    <span class="simbm-mobile-card__value">Rp {{ number_format($row['kas_saldo'], 0, ',', '.') }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Kas laba bln</span>
                    <span class="simbm-mobile-card__value">Rp {{ number_format($row['kas_laba_bulan'], 0, ',', '.') }}</span>
                </div>
                @if ($row['brilink_untung_bulan'] !== null)
                    <div class="simbm-mobile-card__row">
                        <span class="simbm-mobile-card__label">Brilink untung</span>
                        <span class="simbm-mobile-card__value">Rp {{ number_format($row['brilink_untung_bulan'], 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($row['service_laba_bulan'] !== null)
                    <div class="simbm-mobile-card__row">
                        <span class="simbm-mobile-card__label">Service laba</span>
                        <span class="simbm-mobile-card__value">Rp {{ number_format($row['service_laba_bulan'], 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($row['upah_kerja_bulan'] !== null)
                    <div class="simbm-mobile-card__row">
                        <span class="simbm-mobile-card__label">Upah kerja</span>
                        <span class="simbm-mobile-card__value">Rp {{ number_format($row['upah_kerja_bulan'], 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="hidden md:block overflow-x-auto rounded-xl ring-1 ring-white/10 simbm-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-gray-950 border-b border-white/10">
                <tr>
                    <th class="px-3 py-3 text-left">Cabang</th>
                    <th class="px-3 py-3 text-right">Kas Saldo</th>
                    <th class="px-3 py-3 text-right">Kas Laba Bulan</th>
                    <th class="px-3 py-3 text-right">Brilink Untung</th>
                    <th class="px-3 py-3 text-right">Service Laba</th>
                    <th class="px-3 py-3 text-right">Upah Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getRows() as $row)
                    <tr class="border-b border-white/5">
                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-100">{{ $row['branch']->name }}</div>
                            <div class="text-xs text-gray-400">{{ $row['type_label'] }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format($row['kas_saldo'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format($row['kas_laba_bulan'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">
                            {{ $row['brilink_untung_bulan'] !== null ? 'Rp ' . number_format($row['brilink_untung_bulan'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ $row['service_laba_bulan'] !== null ? 'Rp ' . number_format($row['service_laba_bulan'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ $row['upah_kerja_bulan'] !== null ? 'Rp ' . number_format($row['upah_kerja_bulan'], 0, ',', '.') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-filament-panels::page>

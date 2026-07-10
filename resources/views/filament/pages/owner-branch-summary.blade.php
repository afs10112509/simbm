<x-filament-panels::page>

    <div class="simbm-filters sm:grid-cols-2">
        <x-simbm.field label="Bulan">
            <select wire:model.live="month" class="simbm-field__input">
                @foreach (range(1, 12) as $monthOption)
                    <option value="{{ $monthOption }}">
                        {{ \Carbon\Carbon::create(null, $monthOption, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </x-simbm.field>

        <x-simbm.field label="Tahun">
            <select wire:model.live="year" class="simbm-field__input">
                @foreach (range(now()->year - 2, now()->year) as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>
        </x-simbm.field>
    </div>

    <div class="mb-4 text-sm" style="color: var(--simbm-text-muted);">
        Periode: <strong style="color: var(--simbm-text);">{{ $this->getPeriodLabel() }}</strong>
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

    <div class="hidden md:block simbm-table-wrap">
        <table class="simbm-table">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th class="text-right">Kas Saldo</th>
                    <th class="text-right">Kas Laba Bulan</th>
                    <th class="text-right">Brilink Untung</th>
                    <th class="text-right">Service Laba</th>
                    <th class="text-right">Upah Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getRows() as $row)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $row['branch']->name }}</div>
                            <div class="text-xs" style="color: var(--simbm-text-muted);">{{ $row['type_label'] }}</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($row['kas_saldo'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($row['kas_laba_bulan'], 0, ',', '.') }}</td>
                        <td class="text-right">
                            {{ $row['brilink_untung_bulan'] !== null ? 'Rp ' . number_format($row['brilink_untung_bulan'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right">
                            {{ $row['service_laba_bulan'] !== null ? 'Rp ' . number_format($row['service_laba_bulan'], 0, ',', '.') : '—' }}
                        </td>
                        <td class="text-right">
                            {{ $row['upah_kerja_bulan'] !== null ? 'Rp ' . number_format($row['upah_kerja_bulan'], 0, ',', '.') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-filament-panels::page>

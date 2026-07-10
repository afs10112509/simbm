<x-filament-panels::page>

    <div class="simbm-stats-grid sm:grid-cols-2 lg:grid-cols-4">
        <x-simbm.stat-card
            label="Total Harga"
            :value="'Rp ' . number_format($this->getTotalPrice(), 0, ',', '.')"
            tone="primary"
        />
        <x-simbm.stat-card
            label="Total Modal"
            :value="'Rp ' . number_format($this->getTotalModal(), 0, ',', '.')"
            tone="danger"
        />
        <x-simbm.stat-card
            label="Total Laba"
            :value="'Rp ' . number_format($this->getTotalProfit(), 0, ',', '.')"
            tone="success"
        />
        <x-simbm.stat-card
            label="Bagi 2"
            :value="'Rp ' . number_format($this->getSplitProfit(), 0, ',', '.')"
            tone="warning"
        />
    </div>

    <div class="mb-6">
        {{ $this->form }}
    </div>

    <div class="md:hidden">
        @forelse ($this->getRecords() as $record)
            <div class="simbm-mobile-card">
                <div class="simbm-mobile-card__title">
                    {{ $this->formatDateTime($record) }}
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Tukang</span>
                    <span class="simbm-mobile-card__value">{{ $record->technician?->name ?? '-' }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Perangkat</span>
                    <span class="simbm-mobile-card__value">{{ $record->device_brand }} {{ $record->device_type }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Kerusakan</span>
                    <span class="simbm-mobile-card__value">{{ $this->damageLabel($record->damage_type) }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Laba</span>
                    <span class="simbm-mobile-card__value simbm-mobile-card__highlight text-success-600 dark:text-success-400">
                        Rp {{ number_format((float) $record->profit, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="simbm-empty">Tidak ada data service untuk filter yang dipilih.</div>
        @endforelse
    </div>

    <div class="hidden md:block simbm-table-wrap">
        <table class="simbm-table">
            <thead>
                <tr>
                    <th>Tanggal & Jam</th>
                    @if (\App\Support\AccessControl::canViewAllBranches())
                        <th>Cabang</th>
                    @endif
                    <th>Tukang</th>
                    <th>Merek</th>
                    <th>Type</th>
                    <th>Kerusakan</th>
                    <th class="text-right">Modal</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getRecords() as $record)
                    <tr>
                        <td class="whitespace-nowrap">{{ $this->formatDateTime($record) }}</td>
                        @if (\App\Support\AccessControl::canViewAllBranches())
                            <td>{{ $record->branch?->name }}</td>
                        @endif
                        <td>{{ $record->technician?->name ?? '-' }}</td>
                        <td>{{ $record->device_brand }}</td>
                        <td>{{ $record->device_type }}</td>
                        <td>{{ $this->damageLabel($record->damage_type) }}</td>
                        <td class="text-right">Rp {{ number_format((float) $record->modal, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $record->price, 0, ',', '.') }}</td>
                        <td class="text-right font-medium text-success-600 dark:text-success-400">
                            Rp {{ number_format((float) $record->profit, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr class="simbm-table-empty">
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 9 : 8 }}">
                            Tidak ada data service untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->getRecords()->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 6 : 5 }}" class="text-right">Jumlah</td>
                        <td class="text-right text-danger-600 dark:text-danger-400">
                            Rp {{ number_format($this->getTotalModal(), 0, ',', '.') }}
                        </td>
                        <td class="text-right">Rp {{ number_format($this->getTotalPrice(), 0, ',', '.') }}</td>
                        <td class="text-right text-success-600 dark:text-success-400">
                            Rp {{ number_format($this->getTotalProfit(), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 8 : 7 }}" class="text-right text-warning-600 dark:text-warning-400">Bagi 2</td>
                        <td class="text-right text-warning-600 dark:text-warning-400">
                            Rp {{ number_format($this->getSplitProfit(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-filament-panels::page>

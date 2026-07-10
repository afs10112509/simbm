<x-filament-panels::page>

    <x-simbm.stat-card
        class="mb-6"
        label="Total Upah Kerja"
        :value="'Rp ' . number_format($this->getTotalUpah(), 0, ',', '.')"
        tone="primary"
        hint="Berdasarkan filter tanggal dan pekerja di bawah"
    />

    <div class="mb-6">
        {{ $this->form }}
    </div>

    <div class="md:hidden">
        @forelse ($this->getRecords() as $record)
            <div class="simbm-mobile-card">
                <div class="simbm-mobile-card__title">
                    {{ \App\Support\RecordDateTime::forTransaction($record) }}
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Pekerja</span>
                    <span class="simbm-mobile-card__value">{{ $record->worker?->name ?? '-' }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Jasa</span>
                    <span class="simbm-mobile-card__value">{{ $this->getServiceLabel($record->service_type) }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Nominal</span>
                    <span class="simbm-mobile-card__value simbm-mobile-card__highlight text-success-600 dark:text-success-400">
                        Rp {{ number_format((float) $record->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="simbm-empty">Tidak ada data upah kerja untuk filter yang dipilih.</div>
        @endforelse
    </div>

    <div class="hidden md:block simbm-table-wrap">
        <table class="simbm-table">
            <thead>
                <tr>
                    <th>Tanggal & Jam</th>
                    <th>Pekerja</th>
                    <th>Jasa</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getRecords() as $record)
                    <tr>
                        <td>{{ \App\Support\RecordDateTime::forTransaction($record) }}</td>
                        <td>{{ $record->worker?->name ?? '-' }}</td>
                        <td>{{ $this->getServiceLabel($record->service_type) }}</td>
                        <td class="text-right font-medium text-success-600 dark:text-success-400">
                            Rp {{ number_format((float) $record->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr class="simbm-table-empty">
                        <td colspan="4">Tidak ada data upah kerja untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->getRecords()->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right">Jumlah Total</td>
                        <td class="text-right text-primary-600 dark:text-primary-400">
                            Rp {{ number_format($this->getTotalUpah(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-filament-panels::page>

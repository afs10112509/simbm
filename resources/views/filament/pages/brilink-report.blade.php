<x-filament-panels::page>

    <div class="simbm-stats-grid md:grid-cols-2">
        <x-simbm.stat-card
            label="Total Untung Periode"
            :value="'Rp ' . number_format($this->getGrandTotalUntung(), 0, ',', '.')"
            tone="success"
        />
    </div>

    <div class="mb-6">
        {{ $this->form }}
    </div>

    @foreach ($this->getReportSections() as $section)
        @if (($section['gapCount'] ?? 0) > 0)
            <x-simbm.alert>
                <strong>{{ $section['branch']->name }}:</strong>
                {{ $section['gapCount'] }} baris untung kemungkinan mencakup beberapa hari
                karena ada hari tanpa input saldo. Untung harian paling akurat jika diinput setiap hari.
            </x-simbm.alert>
        @endif

        <div class="mb-8">
            <x-simbm.section-header
                :title="$section['branch']->code ?? $section['branch']->name"
                :subtitle="$section['branch']->name"
                meta-label="Total"
                :meta-value="'Rp ' . number_format($section['totalUntung'], 0, ',', '.')"
            />

            <div class="md:hidden">
                @forelse ($section['rows'] as $row)
                    <div class="simbm-mobile-card">
                        <div class="simbm-mobile-card__title">
                            {{ $row['snapshot']->snapshot_date->format('d/m/y') }}
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Kemarin</span>
                            <span class="simbm-mobile-card__value">Rp {{ number_format($row['kemarin'], 0, ',', '.') }}</span>
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Saldo</span>
                            <span class="simbm-mobile-card__value">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</span>
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Untung</span>
                            <span class="simbm-mobile-card__value simbm-mobile-card__highlight {{ $row['untung'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                Rp {{ number_format($row['untung'], 0, ',', '.') }}
                            </span>
                        </div>
                        @if ($row['hasGapWarning'])
                            <div class="mt-2 text-xs text-warning-600 dark:text-warning-400">
                                Lompat {{ $row['missedDays'] }} hari tanpa input
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="simbm-empty">Belum ada data saldo Brilink untuk periode ini.</div>
                @endforelse
            </div>

            <div class="hidden md:block simbm-table-wrap">
                <table class="simbm-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-right">Kemarin</th>
                            <th class="text-right">Saldo</th>
                            <th class="text-right">Untung</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($section['rows'] as $row)
                            <tr>
                                <td class="whitespace-nowrap">{{ $row['snapshot']->snapshot_date->format('d/m/y') }}</td>
                                <td class="text-right">Rp {{ number_format($row['kemarin'], 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                                <td class="text-right font-medium {{ $row['untung'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                    Rp {{ number_format($row['untung'], 0, ',', '.') }}
                                </td>
                                <td class="text-xs text-warning-600 dark:text-warning-400">
                                    @if ($row['hasGapWarning'])
                                        Lompat {{ $row['missedDays'] }} hari tanpa input
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="simbm-table-empty">
                                <td colspan="5">Belum ada data saldo Brilink untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($section['rows']->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right">Total</td>
                                <td class="text-right text-success-600 dark:text-success-400">
                                    Rp {{ number_format($section['totalUntung'], 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endforeach

</x-filament-panels::page>

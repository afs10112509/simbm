<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
        <div class="p-4 rounded-xl bg-gray-950 ring-1 ring-white/10">
            <div class="text-sm text-gray-400">Total Untung Periode</div>
            <div class="mt-1 text-2xl font-bold text-success-400">
                Rp {{ number_format($this->getGrandTotalUntung(), 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="mb-6">
        {{ $this->form }}
    </div>

    @foreach ($this->getReportSections() as $section)
        @if (($section['gapCount'] ?? 0) > 0)
            <div class="p-4 mb-4 text-sm rounded-xl border border-warning-500/30 bg-warning-500/10 text-warning-400">
                <strong>{{ $section['branch']->name }}:</strong>
                {{ $section['gapCount'] }} baris untung kemungkinan mencakup beberapa hari
                karena ada hari tanpa input saldo. Untung harian paling akurat jika diinput setiap hari.
            </div>
        @endif

        <div class="mb-8">
            <div class="flex items-center justify-between px-4 py-3 mb-3 rounded-xl ring-1 ring-white/10 bg-gray-950 simbm-report-header">
                <div>
                    <div class="text-lg font-semibold text-gray-100">
                        {{ $section['branch']->code ?? $section['branch']->name }}
                    </div>
                    <div class="text-sm text-gray-400">{{ $section['branch']->name }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-400">TOTAL</div>
                    <div class="text-lg font-bold text-primary-400">
                        Rp {{ number_format($section['totalUntung'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Tampilan kartu untuk mobile --}}
            <div class="md:hidden">
                @forelse ($section['rows'] as $row)
                    <div class="simbm-mobile-card {{ $row['hasGapWarning'] ? 'border-warning-500/40' : '' }}">
                        <div class="simbm-mobile-card__title">
                            {{ $row['snapshot']->snapshot_date->format('d/m/y') }}
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Kemarin</span>
                            <span class="simbm-mobile-card__value">
                                Rp {{ number_format($row['kemarin'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Saldo</span>
                            <span class="simbm-mobile-card__value">
                                Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="simbm-mobile-card__row">
                            <span class="simbm-mobile-card__label">Untung</span>
                            <span class="simbm-mobile-card__value simbm-mobile-card__highlight {{ $row['untung'] >= 0 ? 'text-success-400' : 'text-danger-400' }}">
                                Rp {{ number_format($row['untung'], 0, ',', '.') }}
                            </span>
                        </div>
                        @if ($row['hasGapWarning'])
                            <div class="mt-2 text-xs text-warning-400">
                                Lompat {{ $row['missedDays'] }} hari tanpa input
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="simbm-mobile-card text-center text-gray-400">
                        Belum ada data saldo Brilink untuk periode ini.
                    </div>
                @endforelse
            </div>

            {{-- Tabel untuk tablet/desktop --}}
            <div class="hidden md:block overflow-x-auto rounded-xl ring-1 ring-white/10 simbm-table-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-gray-950 border-b border-white/10">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium text-gray-300">Tanggal</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-300">Kemarin</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-300">Saldo</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-300">Untung</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-300">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($section['rows'] as $row)
                            <tr class="border-b border-white/5 {{ $row['hasGapWarning'] ? 'bg-warning-500/5' : '' }}">
                                <td class="px-3 py-2 text-gray-100 whitespace-nowrap">
                                    {{ $row['snapshot']->snapshot_date->format('d/m/y') }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-300">
                                    Rp {{ number_format($row['kemarin'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-100">
                                    Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right font-medium {{ $row['untung'] >= 0 ? 'text-success-400' : 'text-danger-400' }}">
                                    Rp {{ number_format($row['untung'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-warning-400 text-xs">
                                    @if ($row['hasGapWarning'])
                                        Lompat {{ $row['missedDays'] }} hari tanpa input
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    Belum ada data saldo Brilink untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($section['rows']->isNotEmpty())
                        <tfoot class="bg-gray-950 border-t border-white/10 font-semibold">
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-right text-gray-200">TOTAL</td>
                                <td class="px-3 py-3 text-right text-success-400">
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

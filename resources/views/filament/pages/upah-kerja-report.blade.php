<x-filament-panels::page>

    <div class="p-4 mb-6 rounded-xl bg-gray-950 ring-1 ring-white/10">
        <div class="text-sm text-gray-400">Total Upah Kerja</div>
        <div class="mt-1 text-2xl font-bold text-primary-500 md:text-3xl">
            Rp {{ number_format($this->getTotalUpah(), 0, ',', '.') }}
        </div>
        <div class="mt-2 text-xs text-gray-500">
            Berdasarkan filter tanggal dan pekerja di bawah
        </div>
    </div>

    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- Kartu mobile --}}
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
                    <span class="simbm-mobile-card__value simbm-mobile-card__highlight text-success-400">
                        Rp {{ number_format((float) $record->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="simbm-mobile-card text-center text-gray-400">
                Tidak ada data upah kerja untuk filter yang dipilih.
            </div>
        @endforelse
    </div>

    {{-- Tabel desktop --}}
    <div class="hidden md:block overflow-x-auto rounded-xl ring-1 ring-white/10 simbm-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-gray-950 border-b border-white/10">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-300">Tanggal & Jam</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-300">Pekerja</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-300">Jasa</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-300">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getRecords() as $record)
                    <tr class="border-b border-white/5">
                        <td class="px-4 py-3 text-gray-100">
                            {{ \App\Support\RecordDateTime::forTransaction($record) }}
                        </td>
                        <td class="px-4 py-3 text-gray-100">
                            {{ $record->worker?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-100">
                            {{ $this->getServiceLabel($record->service_type) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-success-400">
                            Rp {{ number_format((float) $record->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada data upah kerja untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->getRecords()->isNotEmpty())
                <tfoot class="bg-gray-950 border-t border-white/10">
                    <tr>
                        <td colspan="3" class="px-4 py-3 font-semibold text-right text-gray-200">
                            Jumlah Total
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-primary-500">
                            Rp {{ number_format($this->getTotalUpah(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-filament-panels::page>

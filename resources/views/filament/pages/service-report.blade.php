<x-filament-panels::page>

    <div class="grid grid-cols-2 gap-3 mb-6 md:grid-cols-4 md:gap-4">
        <div class="p-3 rounded-xl bg-gray-950 ring-1 ring-white/10 md:p-4">
            <div class="text-xs text-gray-400 md:text-sm">Total Harga</div>
            <div class="mt-1 text-lg font-bold text-primary-500 md:text-xl">
                Rp {{ number_format($this->getTotalPrice(), 0, ',', '.') }}
            </div>
        </div>
        <div class="p-3 rounded-xl bg-gray-950 ring-1 ring-white/10 md:p-4">
            <div class="text-xs text-gray-400 md:text-sm">Total Modal</div>
            <div class="mt-1 text-lg font-bold text-danger-400 md:text-xl">
                Rp {{ number_format($this->getTotalModal(), 0, ',', '.') }}
            </div>
        </div>
        <div class="p-3 rounded-xl bg-gray-950 ring-1 ring-white/10 md:p-4">
            <div class="text-xs text-gray-400 md:text-sm">Total Laba</div>
            <div class="mt-1 text-lg font-bold text-success-400 md:text-xl">
                Rp {{ number_format($this->getTotalProfit(), 0, ',', '.') }}
            </div>
        </div>
        <div class="p-3 rounded-xl bg-gray-950 ring-1 ring-white/10 md:p-4">
            <div class="text-xs text-gray-400 md:text-sm">Bagi 2</div>
            <div class="mt-1 text-lg font-bold text-warning-400 md:text-xl">
                Rp {{ number_format($this->getSplitProfit(), 0, ',', '.') }}
            </div>
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
                    {{ $this->formatDateTime($record) }}
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Tukang</span>
                    <span class="simbm-mobile-card__value">{{ $record->technician?->name ?? '-' }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Perangkat</span>
                    <span class="simbm-mobile-card__value">
                        {{ $record->device_brand }} {{ $record->device_type }}
                    </span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Kerusakan</span>
                    <span class="simbm-mobile-card__value">{{ $this->damageLabel($record->damage_type) }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Laba</span>
                    <span class="simbm-mobile-card__value simbm-mobile-card__highlight text-success-400">
                        Rp {{ number_format((float) $record->profit, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="simbm-mobile-card text-center text-gray-400">
                Tidak ada data service untuk filter yang dipilih.
            </div>
        @endforelse
    </div>

    {{-- Tabel desktop --}}
    <div class="hidden md:block overflow-x-auto rounded-xl ring-1 ring-white/10 simbm-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-gray-950 border-b border-white/10">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-gray-300">Tanggal & Jam</th>
                    @if (\App\Support\AccessControl::canViewAllBranches())
                        <th class="px-3 py-3 text-left font-medium text-gray-300">Cabang</th>
                    @endif
                    <th class="px-3 py-3 text-left font-medium text-gray-300">Tukang</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-300">Merek</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-300">Type</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-300">Kerusakan</th>
                    <th class="px-3 py-3 text-right font-medium text-gray-300">Modal</th>
                    <th class="px-3 py-3 text-right font-medium text-gray-300">Harga</th>
                    <th class="px-3 py-3 text-right font-medium text-gray-300">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getRecords() as $record)
                    <tr class="border-b border-white/5">
                        <td class="px-3 py-2 text-gray-100 whitespace-nowrap">
                            {{ $this->formatDateTime($record) }}
                        </td>
                        @if (\App\Support\AccessControl::canViewAllBranches())
                            <td class="px-3 py-2 text-gray-100">
                                {{ $record->branch?->name }}
                            </td>
                        @endif
                        <td class="px-3 py-2 text-gray-100">
                            {{ $record->technician?->name ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-gray-100">
                            {{ $record->device_brand }}
                        </td>
                        <td class="px-3 py-2 text-gray-100">
                            {{ $record->device_type }}
                        </td>
                        <td class="px-3 py-2 text-gray-100">
                            {{ $this->damageLabel($record->damage_type) }}
                        </td>
                        <td class="px-3 py-2 text-right text-gray-300">
                            Rp {{ number_format((float) $record->modal, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 text-right text-gray-100">
                            Rp {{ number_format((float) $record->price, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 text-right font-medium text-success-400">
                            Rp {{ number_format((float) $record->profit, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 9 : 8 }}" class="px-4 py-8 text-center text-gray-400">
                            Tidak ada data service untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->getRecords()->isNotEmpty())
                <tfoot class="bg-gray-950 border-t border-white/10 font-semibold">
                    <tr>
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 6 : 5 }}" class="px-3 py-3 text-right text-gray-200">
                            Jumlah
                        </td>
                        <td class="px-3 py-3 text-right text-danger-400">
                            Rp {{ number_format($this->getTotalModal(), 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-gray-100">
                            Rp {{ number_format($this->getTotalPrice(), 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-success-400">
                            Rp {{ number_format($this->getTotalProfit(), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="border-t border-white/5">
                        <td colspan="{{ \App\Support\AccessControl::canViewAllBranches() ? 8 : 7 }}" class="px-3 py-3 text-right text-warning-300">
                            Bagi 2
                        </td>
                        <td class="px-3 py-3 text-right text-warning-400">
                            Rp {{ number_format($this->getSplitProfit(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-filament-panels::page>

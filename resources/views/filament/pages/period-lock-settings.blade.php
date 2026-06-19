<x-filament-panels::page>

    <div class="p-4 mb-6 text-sm rounded-xl border border-warning-500/30 bg-warning-500/10 text-warning-300">
        Periode yang dikunci tidak bisa diedit oleh PIC (transaksi, transfer, Brilink, service, upah kerja).
        Pemilik tetap bisa mengubah data.
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-gray-300">Bulan yang akan dikunci</label>
            <select wire:model.live="lockMonth" class="w-full mt-1 rounded-lg border-gray-600 dark:bg-gray-800">
                @foreach (range(1, 12) as $monthOption)
                    <option value="{{ $monthOption }}">
                        {{ \Carbon\Carbon::create(null, $monthOption, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-300">Tahun</label>
            <select wire:model.live="lockYear" class="w-full mt-1 rounded-lg border-gray-600 dark:bg-gray-800">
                @foreach (range(now()->year - 3, now()->year) as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-6 text-sm text-gray-400">
        Periode terpilih: <strong class="text-gray-200">{{ $this->getSelectedPeriodLabel() }}</strong>
        — gunakan tombol <strong>Kunci Periode</strong> di kanan atas.
    </div>

    <div class="overflow-x-auto rounded-xl ring-1 ring-white/10">
        <table class="w-full text-sm">
            <thead class="bg-gray-950 border-b border-white/10">
                <tr>
                    <th class="px-4 py-3 text-left">Periode Terkunci</th>
                    <th class="px-4 py-3 text-left">Dikunci Oleh</th>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getLockedMonths() as $lock)
                    <tr class="border-b border-white/5">
                        <td class="px-4 py-3 font-medium text-gray-100">{{ $lock->label() }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $lock->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $lock->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="unlock({{ $lock->year }}, {{ $lock->month }})"
                                wire:confirm="Buka kunci periode {{ $lock->label() }}?"
                            >
                                Buka Kunci
                            </x-filament::button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            Belum ada periode yang dikunci.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-filament-panels::page>

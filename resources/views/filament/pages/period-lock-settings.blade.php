<x-filament-panels::page>

    <x-simbm.alert class="mb-6">
        Periode yang dikunci tidak bisa diedit oleh PIC (transaksi, transfer, Brilink, service, upah kerja).
        Pemilik tetap bisa mengubah data.
    </x-simbm.alert>

    <div class="simbm-filters sm:grid-cols-2">
        <x-simbm.field label="Bulan yang akan dikunci">
            <select wire:model.live="lockMonth" class="simbm-field__input">
                @foreach (range(1, 12) as $monthOption)
                    <option value="{{ $monthOption }}">
                        {{ \Carbon\Carbon::create(null, $monthOption, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </x-simbm.field>

        <x-simbm.field label="Tahun">
            <select wire:model.live="lockYear" class="simbm-field__input">
                @foreach (range(now()->year - 3, now()->year) as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>
        </x-simbm.field>
    </div>

    <div class="mb-6 text-sm" style="color: var(--simbm-text-muted);">
        Periode terpilih: <strong style="color: var(--simbm-text);">{{ $this->getSelectedPeriodLabel() }}</strong>
        — gunakan tombol <strong>Kunci Periode</strong> di kanan atas.
    </div>

    <div class="simbm-table-wrap">
        <table class="simbm-table">
            <thead>
                <tr>
                    <th>Periode Terkunci</th>
                    <th>Dikunci Oleh</th>
                    <th>Waktu</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getLockedMonths() as $lock)
                    <tr>
                        <td class="font-medium">{{ $lock->label() }}</td>
                        <td>{{ $lock->user?->name ?? '-' }}</td>
                        <td>{{ $lock->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
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
                    <tr class="simbm-table-empty">
                        <td colspan="4">Belum ada periode yang dikunci.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-filament-panels::page>

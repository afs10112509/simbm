<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2">
        <div class="p-4 rounded-xl bg-gray-950 ring-1 ring-white/10">
            <div class="text-sm text-gray-400">Cabang</div>
            <div class="mt-1 text-lg font-semibold text-gray-100">{{ $this->getBranchName() }}</div>
        </div>
        <div class="p-4 rounded-xl bg-gray-950 ring-1 ring-white/10">
            <div class="text-sm text-gray-400">Total Saldo Tampil</div>
            <div class="mt-1 text-2xl font-bold text-primary-400">
                Rp {{ number_format($this->getGrandTotal(), 0, ',', '.') }}
            </div>
        </div>
    </div>

    @foreach ($this->getAccountGroups() as $group)
        <div class="mb-6 overflow-hidden rounded-xl ring-1 ring-white/10">
            <div class="flex items-center justify-between px-4 py-3 bg-gray-950 border-b border-white/10">
                <div class="font-semibold text-gray-100">{{ $group['label'] }}</div>
                <div class="font-bold text-primary-400">
                    Rp {{ number_format($group['total'], 0, ',', '.') }}
                </div>
            </div>

            <div class="divide-y divide-white/5">
                @foreach ($group['accounts'] as $account)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-100">{{ $account->name }}</div>
                            @if (\App\Support\AccessControl::isOwner())
                                <div class="text-xs text-gray-400">{{ $account->branch?->name }}</div>
                            @endif
                        </div>
                        <div class="font-semibold text-success-400 whitespace-nowrap">
                            Rp {{ number_format((float) $account->balance, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if (\App\Support\AccessControl::isOwner())
        <div class="mt-8">
            <h3 class="mb-3 text-lg font-semibold text-gray-100">Riwayat Rekonsiliasi Terbaru</h3>

            <div class="overflow-x-auto rounded-xl ring-1 ring-white/10 simbm-table-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-gray-950 border-b border-white/10">
                        <tr>
                            <th class="px-3 py-3 text-left">Waktu</th>
                            <th class="px-3 py-3 text-left">Akun</th>
                            <th class="px-3 py-3 text-right">Sistem</th>
                            <th class="px-3 py-3 text-right">Fisik</th>
                            <th class="px-3 py-3 text-right">Selisih</th>
                            <th class="px-3 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->getRecentReconciliations() as $item)
                            <tr class="border-b border-white/5">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $item->reconciled_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2">
                                    {{ $item->account?->branch?->name }} — {{ $item->account?->name }}
                                </td>
                                <td class="px-3 py-2 text-right">Rp {{ number_format((float) $item->system_balance, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right">Rp {{ number_format((float) $item->physical_balance, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right {{ (float) $item->difference == 0 ? 'text-success-400' : 'text-warning-400' }}">
                                    Rp {{ number_format((float) $item->difference, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-gray-400">{{ $item->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                    Belum ada rekonsiliasi tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-filament-panels::page>

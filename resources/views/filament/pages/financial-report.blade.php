<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-3 mb-6 sm:grid-cols-3 sm:gap-4">

        <div class="p-4 rounded-xl bg-gray-900">
            <div class="text-sm text-gray-400">
                Total Pemasukan
            </div>

            <div class="text-xl font-bold text-success-500 sm:text-2xl">
                Rp {{ number_format($this->getTotalPemasukan(), 0, ',', '.') }}
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gray-900">
            <div class="text-sm text-gray-400">
                Total Pengeluaran
            </div>

            <div class="text-xl font-bold text-danger-500 sm:text-2xl">
                Rp {{ number_format($this->getTotalPengeluaran(), 0, ',', '.') }}
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gray-900">
            <div class="text-sm text-gray-400">
                Laba
            </div>

            <div class="text-xl font-bold text-primary-500 sm:text-2xl">
                Rp {{ number_format($this->getLaba(), 0, ',', '.') }}
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

        <div>
            <label class="text-sm font-medium">Tanggal Mulai</label>

            <input
                type="date"
                wire:model.live="tanggalMulai"
                class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
            >
        </div>

        <div>
            <label class="text-sm font-medium">Tanggal Selesai</label>

            <input
                type="date"
                wire:model.live="tanggalSelesai"
                class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
            >
        </div>

        <div>
            <label class="text-sm font-medium">Cabang</label>

            <select
                wire:model.live="branchId"
                class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                @disabled(! \App\Support\AccessControl::canViewAllBranches())
            >
                <option value="">
                    Semua Cabang
                </option>

                @foreach ($this->getBranches() as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Jenis</label>

            <select
                wire:model.live="type"
                class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
            >
                <option value="">
                    Semua Jenis
                </option>
                <option value="income">
                    Pemasukan
                </option>
                <option value="expense">
                    Pengeluaran
                </option>
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Kategori</label>

            <select
                wire:model.live="categoryId"
                class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
            >
                <option value="">
                    Semua Kategori
                </option>

                @foreach ($this->getCategories() as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                        @if (! $this->type)
                            ({{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    {{-- Kartu mobile --}}
    <div class="md:hidden">
        @forelse ($this->getTransactions() as $trx)
            <div class="simbm-mobile-card">
                <div class="simbm-mobile-card__title">
                    {{ \App\Support\RecordDateTime::forTransaction($trx) }}
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Cabang</span>
                    <span class="simbm-mobile-card__value">{{ $trx->branch?->name }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Jenis</span>
                    <span class="simbm-mobile-card__value {{ $trx->type === 'income' ? 'text-success-500' : 'text-danger-500' }}">
                        {{ $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                    </span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Kategori</span>
                    <span class="simbm-mobile-card__value">{{ $trx->category?->name }}</span>
                </div>
                <div class="simbm-mobile-card__row">
                    <span class="simbm-mobile-card__label">Nominal</span>
                    <span class="simbm-mobile-card__value simbm-mobile-card__highlight">
                        Rp {{ number_format((float) $trx->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="simbm-mobile-card text-center text-gray-400">
                Tidak ada transaksi untuk filter yang dipilih.
            </div>
        @endforelse
    </div>

    {{-- Tabel desktop --}}
    <div class="hidden md:block overflow-x-auto simbm-table-scroll">

        <table class="w-full text-sm">

            <thead class="border-b border-gray-700">

                <tr>
                    <th class="py-2 text-left">Tanggal & Jam</th>
                    <th class="py-2 text-left">Cabang</th>
                    <th class="py-2 text-left">Jenis</th>
                    <th class="py-2 text-left">Kategori</th>
                    <th class="py-2 text-left">Nominal</th>
                </tr>

            </thead>

            <tbody>

                @forelse ($this->getTransactions() as $trx)

                    <tr class="border-b border-gray-800">

                        <td class="py-2">
                            {{ \App\Support\RecordDateTime::forTransaction($trx) }}
                        </td>

                        <td class="py-2">
                            {{ $trx->branch?->name }}
                        </td>

                        <td class="py-2">

                            @if ($trx->type === 'income')
                                <span class="text-success-500">
                                    Pemasukan
                                </span>
                            @else
                                <span class="text-danger-500">
                                    Pengeluaran
                                </span>
                            @endif

                        </td>

                        <td class="py-2">
                            {{ $trx->category?->name }}
                        </td>

                        <td class="py-2">
                            Rp {{ number_format((float) $trx->amount, 0, ',', '.') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400">
                            Tidak ada transaksi untuk filter yang dipilih.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-filament-panels::page>

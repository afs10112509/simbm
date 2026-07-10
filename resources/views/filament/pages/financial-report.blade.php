<x-filament-panels::page>

    <div class="simbm-stats-grid sm:grid-cols-3">
        <x-simbm.stat-card
            label="Total Pemasukan"
            :value="'Rp ' . number_format($this->getTotalPemasukan(), 0, ',', '.')"
            tone="success"
        />
        <x-simbm.stat-card
            label="Total Pengeluaran"
            :value="'Rp ' . number_format($this->getTotalPengeluaran(), 0, ',', '.')"
            tone="danger"
        />
        <x-simbm.stat-card
            label="Laba"
            :value="'Rp ' . number_format($this->getLaba(), 0, ',', '.')"
            tone="primary"
        />
    </div>

    <div class="simbm-filters sm:grid-cols-2 lg:grid-cols-5">
        <x-simbm.field label="Tanggal Mulai">
            <input type="date" wire:model.live="tanggalMulai" class="simbm-field__input">
        </x-simbm.field>

        <x-simbm.field label="Tanggal Selesai">
            <input type="date" wire:model.live="tanggalSelesai" class="simbm-field__input">
        </x-simbm.field>

        <x-simbm.field label="Cabang">
            <select wire:model.live="branchId" class="simbm-field__input" @disabled(! \App\Support\AccessControl::canViewAllBranches())>
                <option value="">Semua Cabang</option>
                @foreach ($this->getBranches() as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </x-simbm.field>

        <x-simbm.field label="Jenis">
            <select wire:model.live="type" class="simbm-field__input">
                <option value="">Semua Jenis</option>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>
        </x-simbm.field>

        <x-simbm.field label="Kategori">
            <select wire:model.live="categoryId" class="simbm-field__input">
                <option value="">Semua Kategori</option>
                @foreach ($this->getCategories() as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                        @if (! $this->type)
                            ({{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }})
                        @endif
                    </option>
                @endforeach
            </select>
        </x-simbm.field>
    </div>

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
                    <span class="simbm-mobile-card__value {{ $trx->type === 'income' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
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
            <div class="simbm-empty">Tidak ada transaksi untuk filter yang dipilih.</div>
        @endforelse
    </div>

    <div class="hidden md:block simbm-table-wrap">
        <table class="simbm-table">
            <thead>
                <tr>
                    <th>Tanggal & Jam</th>
                    <th>Cabang</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getTransactions() as $trx)
                    <tr>
                        <td>{{ \App\Support\RecordDateTime::forTransaction($trx) }}</td>
                        <td>{{ $trx->branch?->name }}</td>
                        <td class="{{ $trx->type === 'income' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            {{ $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </td>
                        <td>{{ $trx->category?->name }}</td>
                        <td class="text-right font-medium">
                            Rp {{ number_format((float) $trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr class="simbm-table-empty">
                        <td colspan="5">Tidak ada transaksi untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-filament-panels::page>

<x-filament-panels::page>

    @if (! $this->hasBrilinkAccounts())
        <div class="p-4 mb-6 text-sm rounded-xl border border-warning-500/30 bg-warning-500/10 text-warning-400">
            Belum ada akun Brilink untuk cabang ini. Minta pemilik menambahkan akun
            (Cash, Mandiri, Seabank, Nobu, dll.) di <strong>Administrasi → Akun</strong>
            dengan keperluan <strong>Brilink</strong>.
        </div>
    @endif

    {{ $this->form }}

    <x-simbm.mobile-save-bar
        action="simpan"
        label="Simpan Saldo Brilink"
        :disabled="! $this->hasBrilinkAccounts()"
    />

</x-filament-panels::page>

<x-filament-panels::page>

    @if (! $this->hasTechnicians())
        <div class="p-4 mb-6 text-sm rounded-xl border border-warning-500/30 bg-warning-500/10 text-warning-400">
            Belum ada tukang service. Tambahkan di menu
            <strong>Data Tukang Service</strong> terlebih dahulu.
        </div>
    @endif

    {{ $this->form }}

    <x-simbm.mobile-save-bar
        action="simpan"
        label="Simpan Semua Service"
        :disabled="! $this->hasTechnicians()"
    />

</x-filament-panels::page>

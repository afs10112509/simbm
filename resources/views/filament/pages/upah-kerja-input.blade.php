<x-filament-panels::page>

    @if (! $this->hasWorkers())
        <div class="p-4 mb-6 text-sm rounded-xl border border-warning-500/30 bg-warning-500/10 text-warning-400">
            Belum ada data pekerja. Silakan tambahkan di menu
            <strong>Data Pekerja</strong> sebelum input upah kerja.
        </div>
    @endif

    {{ $this->form }}

    <x-simbm.mobile-save-bar
        action="simpan"
        label="Simpan Semua Upah Kerja"
        :disabled="! $this->hasWorkers()"
    />

</x-filament-panels::page>

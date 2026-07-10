<x-filament-panels::page>

    @if (! $this->hasTechnicians())
        <x-simbm.alert class="mb-6">
            Belum ada tukang service. Tambahkan di menu
            <strong>Data Tukang Service</strong> terlebih dahulu.
        </x-simbm.alert>
    @endif

    {{ $this->form }}

    <x-simbm.mobile-save-bar
        action="simpan"
        label="Simpan Semua Service"
        :disabled="! $this->hasTechnicians()"
    />

</x-filament-panels::page>

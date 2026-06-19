@php
    $company = \App\Support\AppSettings::companyName();
    $address = \App\Support\AppSettings::address();
    $phone = \App\Support\AppSettings::phone();
    $email = \App\Support\AppSettings::email();
@endphp

@if ($company || $address || $phone || $email)
    <div class="mt-4 text-center text-sm text-gray-400">
        @if ($company)
            <p class="font-medium text-gray-300">{{ $company }}</p>
        @endif
        @if ($address)
            <p class="mt-1">{{ $address }}</p>
        @endif
        @if ($phone)
            <p class="mt-1">Telp: {{ $phone }}</p>
        @endif
        @if ($email)
            <p class="mt-1">{{ $email }}</p>
        @endif
    </div>
@endif

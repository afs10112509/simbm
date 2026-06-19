<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;

class NominalInput
{
    public static function make(string $name = 'amount', ?string $label = 'Nominal'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->prefix('Rp')
            ->placeholder('Contoh: 1.000')
            ->live()
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'autocomplete' => 'off',
            ])
            ->afterStateUpdated(function ($state, callable $set) use ($name) {
                if ($state === null || $state === '') {
                    return;
                }

                $angka = preg_replace('/[^0-9]/', '', (string) $state);

                if ($angka === '') {
                    $set($name, null);

                    return;
                }

                $set($name, number_format((int) $angka, 0, ',', '.'));
            })
            ->dehydrateStateUsing(function ($state) {
                if (! filled($state)) {
                    return null;
                }

                return preg_replace('/[^0-9]/', '', (string) $state);
            });
    }

    public static function parse(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', (string) $value);
    }
}

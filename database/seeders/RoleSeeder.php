<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(\App\Support\AccessControl::roleLabels()) as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Migrasi role lama hanya jika role tersebut ada (instalasi lama)
        foreach (['kasir', 'admin'] as $legacyRole) {
            if (Role::where('name', $legacyRole)->where('guard_name', 'web')->exists()) {
                User::role($legacyRole)->each(fn (User $user) => $user->syncRoles(['pic']));
            }
        }
    }
}

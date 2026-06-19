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

        User::role('kasir')->each(fn (User $user) => $user->syncRoles(['pic']));
        User::role('admin')->each(fn (User $user) => $user->syncRoles(['pic']));
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BranchSeeder::class,
            CategorySeeder::class,
            AccountSeeder::class,
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Owner',
                'password' => bcrypt('password'),
            ]
        );

        $user->syncRoles(['owner']);

        $this->call([
            PicUserSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;

class PicUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'ilham',
                'email' => 'ilham@gmail.com',
                'password' => 'ilham',
                'branch' => 'Belawa Cell',
            ],
            [
                'name' => 'hasmin',
                'email' => 'hasmin@gmail.com',
                'password' => 'hasmin',
                'branch' => 'Boss Cell',
            ],
            [
                'name' => 'awal',
                'email' => 'awal@gmail.com',
                'password' => 'awal',
                'branch' => 'Gadget Store',
            ],
            [
                'name' => 'aswar',
                'email' => 'aswar@gmail.com',
                'password' => 'aswar',
                'branch' => 'Belawa Maju',
            ],
        ];

        foreach ($users as $data) {
            $branch = Branch::where('name', $data['branch'])->first();

            if (! $branch) {
                $this->command?->warn("Cabang {$data['branch']} tidak ditemukan, lewati {$data['email']}.");

                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'branch_id' => $branch->id,
                ]
            );

            $user->syncRoles([AccessControl::ROLE_PIC]);
        }
    }
}

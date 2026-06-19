<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Belawa Cell',
                'code' => 'BELAWA-CELL',
                'type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Boss Cell',
                'code' => 'BOSS-CELL',
                'type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Gadget Store',
                'code' => 'GADGET-STORE',
                'type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Belawa Maju',
                'code' => 'BELAWA-MAJU',
                'type' => AccessControl::BRANCH_BENGKEL,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch + ['is_active' => true]
            );
        }
    }
}

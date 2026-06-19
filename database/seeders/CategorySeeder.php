<?php

namespace Database\Seeders;

use App\Models\TransactionCategory;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Brilink',
                'slug' => 'brilink',
                'type' => 'income',
                'branch_type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Service',
                'slug' => 'service',
                'type' => 'income',
                'branch_type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Upah Kerja',
                'slug' => 'upah_kerja',
                'type' => 'expense',
                'branch_type' => AccessControl::BRANCH_BENGKEL,
            ],
            [
                'name' => 'Penjualan HP',
                'slug' => null,
                'type' => 'income',
                'branch_type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Penjualan ACC',
                'slug' => null,
                'type' => 'income',
                'branch_type' => AccessControl::BRANCH_KONTER,
            ],
            [
                'name' => 'Listrik',
                'slug' => null,
                'type' => 'expense',
                'branch_type' => null,
            ],
            [
                'name' => 'Dapur',
                'slug' => null,
                'type' => 'expense',
                'branch_type' => null,
            ],
        ];

        foreach ($categories as $category) {
            TransactionCategory::updateOrCreate(
                ['name' => $category['name']],
                $category + ['is_active' => true]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Support\AccessControl;
use App\Support\AccountPurpose;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Branch::query()->each(function (Branch $branch) {
            $accounts = [
                [
                    'name' => 'Kas Umum',
                    'type' => 'cash',
                    'purpose' => AccountPurpose::GENERAL,
                ],
            ];

            if ($branch->type === AccessControl::BRANCH_KONTER) {
                $accounts = array_merge($accounts, [
                    [
                        'name' => 'Cash',
                        'type' => 'cash',
                        'purpose' => AccountPurpose::BRILINK,
                    ],
                    [
                        'name' => 'Mandiri',
                        'type' => 'bank',
                        'purpose' => AccountPurpose::BRILINK,
                    ],
                    [
                        'name' => 'BRI',
                        'type' => 'bank',
                        'purpose' => AccountPurpose::BRILINK,
                    ],
                    [
                        'name' => 'Nobu',
                        'type' => 'bank',
                        'purpose' => AccountPurpose::BRILINK,
                    ],
                    [
                        'name' => 'Seabank',
                        'type' => 'bank',
                        'purpose' => AccountPurpose::BRILINK,
                    ],
                    [
                        'name' => 'Kas Service',
                        'type' => 'cash',
                        'purpose' => AccountPurpose::SERVICE,
                    ],
                ]);

                $this->renameLegacyBrilinkCashAccount($branch);
            }

            if ($branch->type === AccessControl::BRANCH_BENGKEL) {
                $accounts[] = [
                    'name' => 'Kas Upah Kerja',
                    'type' => 'cash',
                    'purpose' => AccountPurpose::UPAH_KERJA,
                ];
            }

            foreach ($accounts as $account) {
                Account::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $account['name'],
                    ],
                    [
                        'type' => $account['type'],
                        'purpose' => $account['purpose'],
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    protected function renameLegacyBrilinkCashAccount(Branch $branch): void
    {
        $hasCash = Account::query()
            ->where('branch_id', $branch->id)
            ->where('name', 'Cash')
            ->forPurpose(AccountPurpose::BRILINK)
            ->exists();

        if ($hasCash) {
            return;
        }

        Account::query()
            ->where('branch_id', $branch->id)
            ->where('name', 'Kas Brilink')
            ->forPurpose(AccountPurpose::BRILINK)
            ->update(['name' => 'Cash']);
    }
}

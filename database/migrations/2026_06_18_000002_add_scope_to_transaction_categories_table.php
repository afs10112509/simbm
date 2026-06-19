<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->string('slug')
                ->nullable()
                ->unique()
                ->after('name');

            $table->string('branch_type')
                ->nullable()
                ->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'branch_type']);
        });
    }
};

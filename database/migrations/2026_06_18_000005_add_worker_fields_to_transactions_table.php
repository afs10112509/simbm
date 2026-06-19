<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('worker_id')
                ->nullable()
                ->after('transaction_category_id')
                ->constrained()
                ->nullOnDelete();

            $table->string('service_type')
                ->nullable()
                ->after('worker_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('worker_id');
            $table->dropColumn('service_type');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brilink_daily_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('total_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'snapshot_date']);
        });

        Schema::create('brilink_daily_snapshot_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brilink_daily_snapshot_id')
                ->constrained('brilink_daily_snapshots')
                ->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['brilink_daily_snapshot_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brilink_daily_snapshot_lines');
        Schema::dropIfExists('brilink_daily_snapshots');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_technician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('income_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('expense_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('service_date');
            $table->string('device_brand');
            $table->string('device_type');
            $table->string('damage_type');
            $table->decimal('modal', 15, 2)->default(0);
            $table->decimal('price', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('identity_number', 16);
            $table->text('address');
            $table->string('birth_place')->nullable();
            $table->date('birth_date');
            $table->string('gender', 20);
            $table->string('position');
            $table->date('joined_at');
            $table->boolean('is_active')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('tax_id', 30)->nullable();
            $table->string('bpjs_health_number', 30)->nullable();
            $table->string('bpjs_employment_number', 30)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('last_education')->nullable();
            $table->string('education_major')->nullable();
            $table->timestamps();

            $table->unique('identity_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

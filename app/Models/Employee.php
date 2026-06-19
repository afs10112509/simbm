<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'branch_id',
        'employee_number',
        'full_name',
        'phone',
        'email',
        'identity_number',
        'address',
        'birth_place',
        'birth_date',
        'gender',
        'position',
        'joined_at',
        'is_active',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'tax_id',
        'bpjs_health_number',
        'bpjs_employment_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'last_education',
        'education_major',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'joined_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

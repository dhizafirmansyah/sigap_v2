<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'address',
        'marital_status',
        'education',
        'pkwt_id',
        'location_id',
        'employee_contract_id',
        'hire_date',
        'contract_start',
        'contract_end',
        'salary',
        'position',
        'department',
        'status',
        'notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'salary' => 'decimal:2',
    ];

    public function pkwt(): BelongsTo
    {
        return $this->belongsTo(Pkwt::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function employeeContract(): BelongsTo
    {
        return $this->belongsTo(EmployeeContract::class);
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'employee_shifts')
                    ->withPivot(['date', 'notes'])
                    ->withTimestamps();
    }

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function turnovers(): HasMany
    {
        return $this->hasMany(Turnover::class);
    }
}

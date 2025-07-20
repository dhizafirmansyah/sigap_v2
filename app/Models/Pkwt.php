<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pkwt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_months',
        'is_renewable',
        'is_active',
    ];

    protected $casts = [
        'is_renewable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'target_production_per_day',
        'quality_standards',
        'is_active',
    ];

    protected $casts = [
        'target_production_per_day' => 'decimal:2',
        'quality_standards' => 'array',
        'is_active' => 'boolean',
    ];

    public function packs(): HasMany
    {
        return $this->hasMany(Pack::class);
    }

    public function kemas(): HasMany
    {
        return $this->hasMany(Kemas::class);
    }
}

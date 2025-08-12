<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shifts')
                    ->withTimestamps();
    }

    // Accessors
    public function getFormattedStartTimeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('H:i');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }

    public function getDurationAttribute(): string
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Handle overnight shifts
        if ($end->lessThan($start)) {
            $end->addDay();
        }
        
        $duration = $start->diffInHours($end);
        return $duration . ' hours';
    }

    public function getShiftTypeAttribute(): string
    {
        $hour = Carbon::parse($this->start_time)->hour;
        
        if ($hour >= 6 && $hour < 14) {
            return 'Morning';
        } elseif ($hour >= 14 && $hour < 22) {
            return 'Afternoon';
        } else {
            return 'Night';
        }
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByType($query, $type)
    {
        switch ($type) {
            case 'morning':
                return $query->whereTime('start_time', '>=', '06:00')
                           ->whereTime('start_time', '<', '14:00');
            case 'afternoon':
                return $query->whereTime('start_time', '>=', '14:00')
                           ->whereTime('start_time', '<', '22:00');
            case 'night':
                return $query->where(function ($q) {
                    $q->whereTime('start_time', '>=', '22:00')
                      ->orWhereTime('start_time', '<', '06:00');
                });
            default:
                return $query;
        }
    }
}

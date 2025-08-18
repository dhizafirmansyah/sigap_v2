<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Presence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'check_in',
        'latitude_checkin',
        'longitude_checkin',
        'photo_checkin',
        'notes_checkin',
        'check_out',
        'latitude_checkout',
        'longitude_checkout',
        'photo_checkout',
        'notes_checkout',
        'work_hours',
        'overtime_hours',
        'status',
        'notes'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'latitude_checkin' => 'decimal:7',
        'longitude_checkin' => 'decimal:7',
        'latitude_checkout' => 'decimal:7',
        'longitude_checkout' => 'decimal:7',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // Accessors
    public function getFormattedCheckInAttribute(): ?string
    {
        return $this->check_in ? $this->check_in->format('d/m/Y H:i') : null;
    }

    public function getFormattedCheckOutAttribute(): ?string
    {
        return $this->check_out ? $this->check_out->format('d/m/Y H:i') : null;
    }

    public function getWorkDurationAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $duration = $this->check_out->diff($this->check_in);
        return $duration->format('%H:%I');
    }

    public function getIsLateAttribute(): bool
    {
        if (!$this->check_in || !$this->shift) {
            return false;
        }

        $shiftStart = Carbon::createFromFormat('H:i:s', $this->shift->start_time);
        $checkInTime = $this->check_in->format('H:i:s');
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);

        return $checkIn->gt($shiftStart);
    }

    public function getIsEarlyCheckoutAttribute(): bool
    {
        if (!$this->check_out || !$this->shift) {
            return false;
        }

        $shiftEnd = Carbon::createFromFormat('H:i:s', $this->shift->end_time);
        $checkOutTime = $this->check_out->format('H:i:s');
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

        return $checkOut->lt($shiftEnd);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('check_in', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('check_in', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('check_in', now()->month)
                    ->whereYear('check_in', now()->year);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    // Methods
    public function calculateWorkHours(): void
    {
        if ($this->check_in && $this->check_out) {
            $workMinutes = $this->check_out->diffInMinutes($this->check_in);
            $this->work_hours = round($workMinutes / 60, 2);
        }
    }

    public function calculateOvertime(): void
    {
        if (!$this->work_hours || !$this->shift) {
            return;
        }

        $shiftDuration = $this->shift->getDurationInHours();
        $overtime = max(0, $this->work_hours - $shiftDuration);
        $this->overtime_hours = round($overtime, 2);
    }

    public function updateStatus(): void
    {
        if ($this->is_late) {
            $this->status = 'late';
        } elseif ($this->check_in && $this->check_out) {
            $this->status = 'present';
        } else {
            $this->status = 'absent';
        }
    }
}

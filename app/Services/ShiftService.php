<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftService
{
    /**
     * Get paginated shifts with filtering and sorting
     */
    public function getPaginatedShifts(array $filters = []): LengthAwarePaginator
    {
        $query = Shift::query()->withCount('employees');

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['shift_type'])) {
            $query->byType($filters['shift_type']);
        }

        if (!empty($filters['start_time_from'])) {
            $query->whereTime('start_time', '>=', $filters['start_time_from']);
        }

        if (!empty($filters['start_time_to'])) {
            $query->whereTime('start_time', '<=', $filters['start_time_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'start_time';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        
        if ($sortBy === 'employees_count') {
            $query->orderBy('employees_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * Get shift statistics
     */
    public function getShiftStatistics(): array
    {
        $totalShifts = Shift::count();
        $activeShifts = Shift::where('is_active', true)->count();
        $inactiveShifts = Shift::where('is_active', false)->count();
        
        $morningShifts = Shift::active()->byType('morning')->count();
        $afternoonShifts = Shift::active()->byType('afternoon')->count();
        $nightShifts = Shift::active()->byType('night')->count();
        
        $totalEmployeesWithShifts = DB::table('employee_shifts')
            ->distinct('employee_id')
            ->count();

        return [
            'total_shifts' => $totalShifts,
            'active_shifts' => $activeShifts,
            'inactive_shifts' => $inactiveShifts,
            'morning_shifts' => $morningShifts,
            'afternoon_shifts' => $afternoonShifts,
            'night_shifts' => $nightShifts,
            'employees_with_shifts' => $totalEmployeesWithShifts,
        ];
    }

    /**
     * Create a new shift
     */
    public function createShift(array $data): Shift
    {
        // Validate time format
        $this->validateShiftTimes($data['start_time'], $data['end_time']);

        return Shift::create([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing shift
     */
    public function updateShift(Shift $shift, array $data): Shift
    {
        // Validate time format
        $this->validateShiftTimes($data['start_time'], $data['end_time']);

        $shift->update([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $shift->fresh();
    }

    /**
     * Delete a shift (only if no active employees assigned)
     */
    public function deleteShift(Shift $shift): bool
    {
        // Check if shift has active employee assignments
        $activeAssignments = DB::table('employee_shifts')
            ->where('shift_id', $shift->id)
            ->count();

        if ($activeAssignments > 0) {
            throw new \Exception('Cannot delete shift with active employee assignments. Please remove all employee assignments first.');
        }

        return $shift->delete();
    }

    /**
     * Duplicate a shift
     */
    public function duplicateShift(Shift $originalShift, array $data): Shift
    {
        return $this->createShift([
            'name' => $data['name'],
            'start_time' => $originalShift->start_time,
            'end_time' => $originalShift->end_time,
            'description' => $originalShift->description,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Toggle shift status
     */
    public function toggleShiftStatus(Shift $shift): Shift
    {
        $shift->update(['is_active' => !$shift->is_active]);
        return $shift->fresh();
    }

    /**
     * Get all active shifts for dropdown/selection
     */
    public function getAllActiveShifts(): Collection
    {
        return Shift::active()
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get shift details with employees
     */
    public function getShiftDetails(Shift $shift): Shift
    {
        return $shift->load([
            'employees' => function ($query) {
                $query->withPivot(['date', 'notes'])
                      ->orderBy('pivot_date', 'desc');
            }
        ]);
    }

    /**
     * Assign employees to shift
     */
    public function assignEmployeesToShift(Shift $shift, array $employeeIds, array $assignmentData = []): void
    {
        $date = $assignmentData['date'] ?? now()->format('Y-m-d');
        $notes = $assignmentData['notes'] ?? null;

        $syncData = [];
        foreach ($employeeIds as $employeeId) {
            $syncData[$employeeId] = [
                'date' => $date,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $shift->employees()->syncWithoutDetaching($syncData);
    }

    /**
     * Remove employee from shift
     */
    public function removeEmployeeFromShift(Shift $shift, Employee $employee, string $date): void
    {
        $shift->employees()->wherePivot('date', $date)->detach($employee->id);
    }

    /**
     * Get employees available for shift assignment
     */
    public function getAvailableEmployees(Shift $shift, string $date): Collection
    {
        $assignedEmployeeIds = DB::table('employee_shifts')
            ->where('shift_id', $shift->id)
            ->where('date', $date)
            ->pluck('employee_id');

        return Employee::whereNotIn('id', $assignedEmployeeIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get shift conflicts for employee
     */
    public function getShiftConflicts(int $employeeId, string $date, ?int $excludeShiftId = null): Collection
    {
        $query = Shift::whereHas('employees', function($q) use ($employeeId, $date) {
            $q->where('employees.id', $employeeId)
              ->where('employee_shifts.date', $date);
        })->where('is_active', true);

        if ($excludeShiftId) {
            $query->where('id', '!=', $excludeShiftId);
        }

        return $query->get();
    }

    /**
     * Validate shift times
     */
    private function validateShiftTimes(string $startTime, string $endTime): void
    {
        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
        } catch (\Exception $e) {
            throw new \Exception('Invalid time format. Please use HH:MM format.');
        }

        // Allow overnight shifts, but ensure they're not the same time
        if ($start->format('H:i') === $end->format('H:i')) {
            throw new \Exception('Start time and end time cannot be the same.');
        }
    }

    /**
     * Get shift schedule for date range
     */
    public function getShiftSchedule(string $startDate, string $endDate): array
    {
        $shifts = Shift::active()
            ->with(['employees' => function ($query) use ($startDate, $endDate) {
                $query->wherePivot('date', '>=', $startDate)
                      ->wherePivot('date', '<=', $endDate);
            }])
            ->orderBy('start_time')
            ->get();

        return $shifts->map(function ($shift) {
            return [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => $shift->formatted_start_time,
                'end_time' => $shift->formatted_end_time,
                'duration' => $shift->duration,
                'type' => $shift->shift_type,
                'employees' => $shift->employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'date' => $employee->pivot->date,
                        'notes' => $employee->pivot->notes,
                    ];
                }),
            ];
        })->toArray();
    }
}

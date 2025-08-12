<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
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
        $query = Shift::query()->withCount('users');

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
        
        if ($sortBy === 'users_count') {
            $query->orderBy('users_count', $sortOrder);
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
        
        $totalUsersWithShifts = DB::table('user_shifts')
            ->where('is_active', true)
            ->distinct('user_id')
            ->count();

        return [
            'total_shifts' => $totalShifts,
            'active_shifts' => $activeShifts,
            'inactive_shifts' => $inactiveShifts,
            'morning_shifts' => $morningShifts,
            'afternoon_shifts' => $afternoonShifts,
            'night_shifts' => $nightShifts,
            'users_with_shifts' => $totalUsersWithShifts,
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
     * Delete a shift (only if no active users assigned)
     */
    public function deleteShift(Shift $shift): bool
    {
        // Check if shift has active user assignments
        $activeAssignments = DB::table('user_shifts')
            ->where('shift_id', $shift->id)
            ->where('is_active', true)
            ->count();

        if ($activeAssignments > 0) {
            throw new \Exception('Cannot delete shift with active user assignments. Please remove all user assignments first.');
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
     * Get shift details with users
     */
    public function getShiftDetails(Shift $shift): Shift
    {
        return $shift->load([
            'users' => function ($query) {
                $query->withPivot(['start_date', 'end_date', 'is_active', 'notes'])
                      ->orderBy('pivot_start_date', 'desc');
            }
        ]);
    }

    /**
     * Assign users to shift
     */
    public function assignUsersToShift(Shift $shift, array $userIds, array $assignmentData = []): void
    {
        $startDate = $assignmentData['start_date'] ?? now()->format('Y-m-d');
        $endDate = $assignmentData['end_date'] ?? null;
        $notes = $assignmentData['notes'] ?? null;

        $syncData = [];
        foreach ($userIds as $userId) {
            $syncData[$userId] = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => true,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $shift->users()->syncWithoutDetaching($syncData);
    }

    /**
     * Remove user from shift
     */
    public function removeUserFromShift(Shift $shift, User $user): void
    {
        $shift->users()->updateExistingPivot($user->id, [
            'is_active' => false,
            'end_date' => now()->format('Y-m-d'),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get users available for shift assignment
     */
    public function getAvailableUsers(Shift $shift): Collection
    {
        $assignedUserIds = DB::table('user_shifts')
            ->where('shift_id', $shift->id)
            ->where('is_active', true)
            ->pluck('user_id');

        return User::whereNotIn('id', $assignedUserIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get shift conflicts for user
     */
    public function getShiftConflicts(int $userId, string $startTime, string $endTime, ?int $excludeShiftId = null): Collection
    {
        $userShiftIds = DB::table('user_shifts')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('shift_id');

        $query = Shift::whereIn('id', $userShiftIds)
            ->where('is_active', true);

        if ($excludeShiftId) {
            $query->where('id', '!=', $excludeShiftId);
        }

        // Check for time conflicts
        $query->where(function ($q) use ($startTime, $endTime) {
            $q->where(function ($subQ) use ($startTime, $endTime) {
                // New shift starts during existing shift
                $subQ->whereTime('start_time', '<=', $startTime)
                     ->whereTime('end_time', '>', $startTime);
            })->orWhere(function ($subQ) use ($startTime, $endTime) {
                // New shift ends during existing shift
                $subQ->whereTime('start_time', '<', $endTime)
                     ->whereTime('end_time', '>=', $endTime);
            })->orWhere(function ($subQ) use ($startTime, $endTime) {
                // New shift encompasses existing shift
                $subQ->whereTime('start_time', '>=', $startTime)
                     ->whereTime('end_time', '<=', $endTime);
            });
        });

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
            ->with(['users' => function ($query) use ($startDate, $endDate) {
                $query->wherePivot('is_active', true)
                      ->wherePivot('start_date', '<=', $endDate)
                      ->where(function ($q) use ($startDate) {
                          $q->wherePivot('end_date', '>=', $startDate)
                            ->orWherePivot('end_date', null);
                      });
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
                'users' => $shift->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'start_date' => $user->pivot->start_date,
                        'end_date' => $user->pivot->end_date,
                        'notes' => $user->pivot->notes,
                    ];
                }),
            ];
        })->toArray();
    }
}

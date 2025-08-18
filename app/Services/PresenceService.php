<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PresenceService
{
    /**
     * Get paginated presences with filters
     */
    public function getPaginatedPresences(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Presence::query()
            ->with(['employee', 'shift'])
            ->orderBy('check_in', 'desc');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('check_in', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('check_in', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get presence statistics
     */
    public function getPresenceStatistics(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $totalToday = Presence::whereDate('check_in', $today)->count();
        $totalThisMonth = Presence::where('check_in', '>=', $thisMonth)->count();
        
        $presentToday = Presence::whereDate('check_in', $today)
            ->where('status', 'present')
            ->count();
            
        $lateToday = Presence::whereDate('check_in', $today)
            ->where('status', 'late')
            ->count();

        $avgWorkHours = Presence::where('check_in', '>=', $thisMonth)
            ->whereNotNull('work_hours')
            ->avg('work_hours');

        $totalOvertime = Presence::where('check_in', '>=', $thisMonth)
            ->sum('overtime_hours');

        return [
            'total_today' => $totalToday,
            'total_this_month' => $totalThisMonth,
            'present_today' => $presentToday,
            'late_today' => $lateToday,
            'avg_work_hours' => round($avgWorkHours ?? 0, 2),
            'total_overtime' => round($totalOvertime ?? 0, 2),
        ];
    }

    /**
     * Create new presence
     */
    public function createPresence(array $data): Presence
    {
        return DB::transaction(function () use ($data) {
            $presence = Presence::create($data);
            
            // Calculate work hours if both check_in and check_out are present
            if (isset($data['check_out'])) {
                $presence->calculateWorkHours();
                $presence->calculateOvertime();
                $presence->updateStatus();
                $presence->save();
            }

            return $presence->load(['employee', 'shift']);
        });
    }

    /**
     * Update presence
     */
    public function updatePresence(Presence $presence, array $data): Presence
    {
        return DB::transaction(function () use ($presence, $data) {
            $presence->update($data);
            
            // Recalculate if times are updated
            if (isset($data['check_in']) || isset($data['check_out'])) {
                $presence->calculateWorkHours();
                $presence->calculateOvertime();
                $presence->updateStatus();
                $presence->save();
            }

            return $presence->load(['employee', 'shift']);
        });
    }

    /**
     * Delete presence
     */
    public function deletePresence(Presence $presence): bool
    {
        return $presence->delete();
    }

    /**
     * Get presence details
     */
    public function getPresenceDetails(Presence $presence): Presence
    {
        return $presence->load(['employee', 'shift']);
    }

    /**
     * Check in employee
     */
    public function checkIn(array $data): Presence
    {
        $presence = $this->createPresence([
            'employee_id' => $data['employee_id'],
            'shift_id' => $data['shift_id'],
            'check_in' => $data['check_in'] ?? now(),
            'latitude_checkin' => $data['latitude_checkin'] ?? null,
            'longitude_checkin' => $data['longitude_checkin'] ?? null,
            'photo_checkin' => $data['photo_checkin'] ?? null,
            'notes_checkin' => $data['notes_checkin'] ?? null,
            'status' => 'partial', // Will be updated when calculateStatus is called
        ]);

        $presence->updateStatus();
        $presence->save();

        return $presence;
    }

    /**
     * Check out employee
     */
    public function checkOut(Presence $presence, array $data): Presence
    {
        return $this->updatePresence($presence, [
            'check_out' => $data['check_out'] ?? now(),
            'latitude_checkout' => $data['latitude_checkout'] ?? null,
            'longitude_checkout' => $data['longitude_checkout'] ?? null,
            'photo_checkout' => $data['photo_checkout'] ?? null,
            'notes_checkout' => $data['notes_checkout'] ?? null,
        ]);
    }

    /**
     * Get available employees for presence
     */
    public function getAvailableEmployees(): Collection
    {
        return Employee::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'position']);
    }

    /**
     * Get available shifts
     */
    public function getAvailableShifts(): Collection
    {
        return Shift::where('is_active', true)
            ->orderBy('start_time')
            ->get(['id', 'name', 'start_time', 'end_time']);
    }

    /**
     * Get employee presence for specific date
     */
    public function getEmployeePresenceForDate(int $employeeId, string $date): ?Presence
    {
        return Presence::where('employee_id', $employeeId)
            ->whereDate('check_in', $date)
            ->first();
    }

    /**
     * Get presence conflicts for employee
     */
    public function getPresenceConflicts(int $employeeId, string $date, ?int $excludePresenceId = null): Collection
    {
        $query = Presence::where('employee_id', $employeeId)
            ->whereDate('check_in', $date);

        if ($excludePresenceId) {
            $query->where('id', '!=', $excludePresenceId);
        }

        return $query->get();
    }

    /**
     * Get daily presence report
     */
    public function getDailyPresenceReport(string $date): array
    {
        $presences = Presence::with(['employee', 'shift'])
            ->whereDate('check_in', $date)
            ->orderBy('check_in')
            ->get();

        $totalEmployees = Employee::where('status', 'active')->count();
        $presentCount = $presences->count();
        $absentCount = $totalEmployees - $presentCount;

        $statusCounts = $presences->groupBy('status')->map(function ($group) {
            return $group->count();
        });

        return [
            'date' => $date,
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'status_breakdown' => $statusCounts,
            'presences' => $presences,
        ];
    }

    /**
     * Get monthly presence summary
     */
    public function getMonthlyPresenceSummary(int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $presences = Presence::with(['employee'])
            ->whereBetween('check_in', [$startDate, $endDate])
            ->get();

        $totalDays = $endDate->day;
        $workingDays = $this->getWorkingDaysInMonth($year, $month);

        $summary = $presences->groupBy('employee_id')->map(function ($employeePresences) use ($workingDays) {
            $employee = $employeePresences->first()->employee;
            $totalPresent = $employeePresences->count();
            $totalLate = $employeePresences->where('status', 'late')->count();
            $totalWorkHours = $employeePresences->sum('work_hours');
            $totalOvertime = $employeePresences->sum('overtime_hours');
            $attendanceRate = $workingDays > 0 ? round(($totalPresent / $workingDays) * 100, 2) : 0;

            return [
                'employee' => $employee,
                'total_present' => $totalPresent,
                'total_absent' => max(0, $workingDays - $totalPresent),
                'total_late' => $totalLate,
                'total_work_hours' => round($totalWorkHours, 2),
                'total_overtime' => round($totalOvertime, 2),
                'attendance_rate' => $attendanceRate,
            ];
        });

        return [
            'year' => $year,
            'month' => $month,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'summary' => $summary,
        ];
    }

    /**
     * Calculate working days in a month (excluding weekends)
     */
    private function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}

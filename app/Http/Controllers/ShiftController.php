<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Employee;
use App\Services\ShiftService;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    use HasPermissions;

    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Display shift management dashboard
     */
    public function index(Request $request): Response
    {
        $this->authorizePermission('view_shifts');

        $filters = $request->only([
            'search', 'is_active', 'shift_type', 'start_time_from', 'start_time_to',
            'sort_by', 'sort_order', 'per_page'
        ]);

        $shifts = $this->shiftService->getPaginatedShifts($filters);
        $statistics = $this->shiftService->getShiftStatistics();

        return Inertia::render('Shift/Shift', [
            'shifts' => $shifts,
            'statistics' => $statistics,
            'filters' => $filters,
            'user' => $request->user()
        ]);
    }

    /**
     * Show shift details
     */
    public function show(Shift $shift): Response
    {
        $this->authorizePermission('view_shifts');

        $shiftDetails = $this->shiftService->getShiftDetails($shift);
        $availableEmployees = $this->shiftService->getAvailableEmployees($shift, now()->format('Y-m-d'));

        return Inertia::render('Shift/ShiftDetail', [
            'shift' => $shiftDetails,
            'availableEmployees' => $availableEmployees
        ]);
    }

    /**
     * Store a new shift
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('create_shifts');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $this->shiftService->createShift($validated);

            return redirect()->route('shifts.index')->with('success', 'Shift created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an existing shift
     */
    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission('edit_shifts');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shifts', 'name')->ignore($shift->id)],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $this->shiftService->updateShift($shift, $validated);

            return redirect()->route('shifts.index')->with('success', 'Shift updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a shift
     */
    public function destroy(Shift $shift): RedirectResponse
    {
        $this->authorizePermission('delete_shifts');

        try {
            $this->shiftService->deleteShift($shift);

            return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Duplicate a shift
     */
    public function duplicate(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission('create_shifts');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name',
            'is_active' => 'boolean',
        ]);

        try {
            $this->shiftService->duplicateShift($shift, $validated);

            return redirect()->route('shifts.index')->with('success', 'Shift duplicated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle shift status
     */
    public function toggleStatus(Shift $shift): RedirectResponse
    {
        $this->authorizePermission('edit_shifts');

        try {
            $this->shiftService->toggleShiftStatus($shift);

            $status = $shift->fresh()->is_active ? 'activated' : 'deactivated';
            return redirect()->route('shifts.index')->with('success', "Shift {$status} successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Assign employees to shift
     */
    public function assignEmployees(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission('assign_shift_users');

        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Check for shift conflicts
            foreach ($validated['employee_ids'] as $employeeId) {
                $conflicts = $this->shiftService->getShiftConflicts(
                    $employeeId,
                    $validated['date'],
                    $shift->id
                );

                if ($conflicts->isNotEmpty()) {
                    $employee = Employee::find($employeeId);
                    $conflictNames = $conflicts->pluck('name')->join(', ');
                    return redirect()->back()->withErrors([
                        'error' => "Employee {$employee->name} has conflicting shifts on this date: {$conflictNames}"
                    ]);
                }
            }

            $this->shiftService->assignEmployeesToShift($shift, $validated['employee_ids'], [
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $employeeCount = count($validated['employee_ids']);
            return redirect()->route('shifts.show', $shift)->with('success', 
                "{$employeeCount} employee(s) assigned to shift successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove employee from shift
     */
    public function removeEmployee(Request $request, Shift $shift, Employee $employee): RedirectResponse
    {
        $this->authorizePermission('assign_shift_users');

        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        try {
            $this->shiftService->removeEmployeeFromShift($shift, $employee, $validated['date']);

            return redirect()->route('shifts.show', $shift)->with('success', 
                'Employee removed from shift successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get shift schedule for calendar view
     */
    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $schedule = $this->shiftService->getShiftSchedule(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($schedule);
    }

    /**
     * Get available employees for shift assignment
     */
    public function availableEmployees(Request $request, Shift $shift)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $employees = $this->shiftService->getAvailableEmployees($shift, $date);
        return response()->json($employees);
    }

    /**
     * Check shift conflicts for employee
     */
    public function checkConflicts(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        $conflicts = $this->shiftService->getShiftConflicts(
            $validated['employee_id'],
            $validated['date'],
            $shift->id
        );

        return response()->json([
            'has_conflicts' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts->map(function ($conflict) {
                return [
                    'id' => $conflict->id,
                    'name' => $conflict->name,
                    'start_time' => $conflict->start_time,
                    'end_time' => $conflict->end_time,
                ];
            })
        ]);
    }
}

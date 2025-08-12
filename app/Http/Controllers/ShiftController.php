<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
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
        $availableUsers = $this->shiftService->getAvailableUsers($shift);

        return Inertia::render('Shift/ShiftDetail', [
            'shift' => $shiftDetails,
            'availableUsers' => $availableUsers
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
     * Assign users to shift
     */
    public function assignUsers(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission('assign_shift_users');

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Check for shift conflicts
            foreach ($validated['user_ids'] as $userId) {
                $conflicts = $this->shiftService->getShiftConflicts(
                    $userId,
                    $shift->start_time,
                    $shift->end_time,
                    $shift->id
                );

                if ($conflicts->isNotEmpty()) {
                    $user = User::find($userId);
                    $conflictNames = $conflicts->pluck('name')->join(', ');
                    return redirect()->back()->withErrors([
                        'error' => "User {$user->name} has conflicting shifts: {$conflictNames}"
                    ]);
                }
            }

            $this->shiftService->assignUsersToShift($shift, $validated['user_ids'], [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $userCount = count($validated['user_ids']);
            return redirect()->route('shifts.show', $shift)->with('success', 
                "{$userCount} user(s) assigned to shift successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove user from shift
     */
    public function removeUser(Request $request, Shift $shift, User $user): RedirectResponse
    {
        $this->authorizePermission('assign_shift_users');

        try {
            $this->shiftService->removeUserFromShift($shift, $user);

            return redirect()->route('shifts.show', $shift)->with('success', 
                'User removed from shift successfully!');
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
     * Get available users for shift assignment
     */
    public function availableUsers(Shift $shift)
    {
        $users = $this->shiftService->getAvailableUsers($shift);
        return response()->json($users);
    }

    /**
     * Check shift conflicts for user
     */
    public function checkConflicts(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $conflicts = $this->shiftService->getShiftConflicts(
            $validated['user_id'],
            $shift->start_time,
            $shift->end_time,
            $shift->id
        );

        return response()->json([
            'has_conflicts' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts->map(function ($conflictShift) {
                return [
                    'id' => $conflictShift->id,
                    'name' => $conflictShift->name,
                    'start_time' => $conflictShift->formatted_start_time,
                    'end_time' => $conflictShift->formatted_end_time,
                ];
            })
        ]);
    }
}

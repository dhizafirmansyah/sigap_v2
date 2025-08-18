<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Services\PresenceService;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PresenceController extends Controller
{
    use HasPermissions;
    public function __construct(
        protected PresenceService $presenceService
    ) {}

    /**
     * Display presence list
     */
    public function index(Request $request): Response
    {
        $this->authorizePermission('view_presences');

        $filters = $request->only([
            'search', 'employee_id', 'shift_id', 'status', 
            'date_from', 'date_to'
        ]);

        $presences = $this->presenceService->getPaginatedPresences($filters, $request->get('per_page', 15));
        $statistics = $this->presenceService->getPresenceStatistics();
        $availableEmployees = $this->presenceService->getAvailableEmployees();
        $availableShifts = $this->presenceService->getAvailableShifts();

        return Inertia::render('Presence/Presence', [
            'presences' => $presences,
            'statistics' => $statistics,
            'availableEmployees' => $availableEmployees,
            'availableShifts' => $availableShifts,
            'filters' => $filters,
            'user' => $request->user()
        ]);
    }

    /**
     * Show presence details
     */
    public function show(Presence $presence): Response
    {
        $this->authorizePermission('view_presences');

        $presenceDetails = $this->presenceService->getPresenceDetails($presence);

        return Inertia::render('Presence/PresenceDetail', [
            'presence' => $presenceDetails,
        ]);
    }

    /**
     * Show create presence form
     */
    public function create(): Response
    {
        $this->authorizePermission('create_presences');

        $availableEmployees = $this->presenceService->getAvailableEmployees();
        $availableShifts = $this->presenceService->getAvailableShifts();

        return Inertia::render('Presence/CreatePresence', [
            'availableEmployees' => $availableEmployees,
            'availableShifts' => $availableShifts,
        ]);
    }

    /**
     * Store new presence
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('create_presences');

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after:check_in',
            'latitude_checkin' => 'nullable|numeric',
            'longitude_checkin' => 'nullable|numeric',
            'photo_checkin' => 'nullable|string',
            'notes_checkin' => 'nullable|string|max:500',
            'latitude_checkout' => 'nullable|numeric',
            'longitude_checkout' => 'nullable|numeric',
            'photo_checkout' => 'nullable|string',
            'notes_checkout' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Check for conflicts
            $conflicts = $this->presenceService->getPresenceConflicts(
                $validated['employee_id'],
                date('Y-m-d', strtotime($validated['check_in']))
            );

            if ($conflicts->isNotEmpty()) {
                return redirect()->back()->withErrors([
                    'error' => 'Employee already has presence record for this date.'
                ]);
            }

            $presence = $this->presenceService->createPresence($validated);

            return redirect()->route('presences.index')->with('success', 
                'Presence record created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show edit presence form
     */
    public function edit(Presence $presence): Response
    {
        $this->authorizePermission('edit_presences');

        $presenceDetails = $this->presenceService->getPresenceDetails($presence);
        $availableEmployees = $this->presenceService->getAvailableEmployees();
        $availableShifts = $this->presenceService->getAvailableShifts();

        return Inertia::render('Presence/EditPresence', [
            'presence' => $presenceDetails,
            'availableEmployees' => $availableEmployees,
            'availableShifts' => $availableShifts,
        ]);
    }

    /**
     * Update presence
     */
    public function update(Request $request, Presence $presence): RedirectResponse
    {
        $this->authorizePermission('edit_presences');

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'required|date',
            'check_out' => 'nullable|date|after:check_in',
            'latitude_checkin' => 'nullable|numeric',
            'longitude_checkin' => 'nullable|numeric',
            'photo_checkin' => 'nullable|string',
            'notes_checkin' => 'nullable|string|max:500',
            'latitude_checkout' => 'nullable|numeric',
            'longitude_checkout' => 'nullable|numeric',
            'photo_checkout' => 'nullable|string',
            'notes_checkout' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Check for conflicts (excluding current presence)
            $conflicts = $this->presenceService->getPresenceConflicts(
                $validated['employee_id'],
                date('Y-m-d', strtotime($validated['check_in'])),
                $presence->id
            );

            if ($conflicts->isNotEmpty()) {
                return redirect()->back()->withErrors([
                    'error' => 'Employee already has another presence record for this date.'
                ]);
            }

            $this->presenceService->updatePresence($presence, $validated);

            return redirect()->route('presences.index')->with('success', 
                'Presence record updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete presence
     */
    public function destroy(Presence $presence): RedirectResponse
    {
        $this->authorizePermission('delete_presences');

        try {
            $this->presenceService->deletePresence($presence);

            return redirect()->route('presences.index')->with('success', 
                'Presence record deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Quick check-in for employee
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $this->authorizePermission('create_presences');

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'latitude_checkin' => 'nullable|numeric',
            'longitude_checkin' => 'nullable|numeric',
            'photo_checkin' => 'nullable|string',
            'notes_checkin' => 'nullable|string|max:500',
        ]);

        try {
            // Check if employee already checked in today
            $existingPresence = $this->presenceService->getEmployeePresenceForDate(
                $validated['employee_id'],
                now()->format('Y-m-d')
            );

            if ($existingPresence) {
                return redirect()->back()->withErrors([
                    'error' => 'Employee already has presence record for today.'
                ]);
            }

            $presence = $this->presenceService->checkIn($validated);

            return redirect()->route('presences.index')->with('success', 
                'Employee checked in successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Quick check-out for employee
     */
    public function checkOut(Request $request, Presence $presence): RedirectResponse
    {
        $this->authorizePermission('edit_presences');

        $validated = $request->validate([
            'latitude_checkout' => 'nullable|numeric',
            'longitude_checkout' => 'nullable|numeric',
            'photo_checkout' => 'nullable|string',
            'notes_checkout' => 'nullable|string|max:500',
        ]);

        try {
            if ($presence->check_out) {
                return redirect()->back()->withErrors([
                    'error' => 'Employee already checked out.'
                ]);
            }

            $this->presenceService->checkOut($presence, $validated);

            return redirect()->route('presences.index')->with('success', 
                'Employee checked out successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get daily presence report
     */
    public function dailyReport(Request $request)
    {
        $this->authorizePermission('view_reports');

        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        $report = $this->presenceService->getDailyPresenceReport($validated['date']);

        return response()->json($report);
    }

    /**
     * Get monthly presence summary
     */
    public function monthlyReport(Request $request)
    {
        $this->authorizePermission('view_reports');

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $report = $this->presenceService->getMonthlyPresenceSummary(
            $validated['year'],
            $validated['month']
        );

        return response()->json($report);
    }
}

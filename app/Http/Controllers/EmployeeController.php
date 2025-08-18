<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Location;
use App\Models\Pkwt;
use App\Models\EmployeeContract;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['location', 'pkwt', 'employeeContract', 'employeeShifts.shift']);

        // Global search across all fields
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhereHas('location', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('department')) {
            $query->where('department', 'like', "%{$request->input('department')}%");
        }

        $employees = $query->paginate(10)->withQueryString();

        $data = [
            'employees' => EmployeeResource::collection($employees),
            'locations' => Location::where('is_active', true)->get(['id', 'name']),
            'contracts' => EmployeeContract::where('is_active', true)->get(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'location_id', 'department']),
        ];

        // Return JSON only for explicit fetch API requests (not for Inertia navigation)
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => $employees,
                'props' => $data
            ]);
        }

        return Inertia::render('Employees/Index', $data);
    }

    /**
     * Search for autocomplete - returns all matching records
     */
    public function search(Request $request)
    {
        $query = Employee::with(['location', 'pkwt', 'employeeContract', 'employeeShifts.shift']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $employees = $query->limit(20)->get();

        return EmployeeResource::collection($employees);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Employees/Create', [
            'locations' => Location::where('is_active', true)->get(['id', 'name']),
            'pkwts' => Pkwt::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|string|unique:employees',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:employees',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:male,female,other',
                'birth_date' => 'nullable|date',
                'address' => 'nullable|string',
                'marital_status' => 'nullable|in:single,married,divorced,other',
                'education' => 'nullable|in:sd,smp,sma,diploma,sarjana,other',
                'pkwt_id' => 'nullable|exists:pkwts,id',
                'location_id' => 'required|exists:locations,id',
                'hire_date' => 'required|date',
                'contract_start' => 'nullable|date',
                'contract_end' => 'nullable|date',
                'salary' => 'nullable|numeric|min:0',
                'position' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'status' => 'required|in:active,inactive,terminated,resigned',
                'notes' => 'nullable|string',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relation' => 'nullable|string|max:100',
            ]);

            $employee = Employee::create($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee created successfully',
                    'data' => new EmployeeResource($employee)
                ], 201);
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating employee'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Employee $employee)
    {
        $employee->load(['location', 'pkwt', 'employeeContract', 'employeeShifts.shift', 'presences' => function($query) {
            $query->latest()->limit(10);
        }]);

        // Return JSON for fetch API requests
        if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee)
            ]);
        }

        // For Inertia show page, send the employee data directly (not wrapped in Resource)
        return Inertia::render('Employees/Show', [
            'employee' => $employee->load(['location', 'pkwt', 'employeeContract', 'employeeShifts.shift']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $employee->load(['location', 'pkwt', 'employeeContract']);
        
        return Inertia::render('Employees/Edit', [
            'employee' => new EmployeeResource($employee),
            'locations' => Location::where('is_active', true)->get(['id', 'name']),
            'pkwts' => Pkwt::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|string|unique:employees,employee_id,' . $employee->getKey(),
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:employees,email,' . $employee->getKey(),
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:male,female,other',
                'birth_date' => 'nullable|date',
                'address' => 'nullable|string',
                'marital_status' => 'nullable|in:single,married,divorced,other',
                'education' => 'nullable|in:sd,smp,sma,diploma,sarjana,other',
                'pkwt_id' => 'nullable|exists:pkwts,id',
                'location_id' => 'required|exists:locations,id',
                'hire_date' => 'required|date',
                'contract_start' => 'nullable|date',
                'contract_end' => 'nullable|date',
                'salary' => 'nullable|numeric|min:0',
                'position' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'status' => 'required|in:active,inactive,terminated,resigned',
                'notes' => 'nullable|string',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relation' => 'nullable|string|max:100',
            ]);

            $employee->update($validated);

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee updated successfully',
                    'data' => new EmployeeResource($employee->fresh(['location', 'pkwt', 'employeeContract']))
                ]);
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating employee'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Employee $employee)
    {
        try {
            $employee->delete();

            // Return JSON only for explicit fetch API requests (not for Inertia navigation)
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee deleted successfully'
                ]);
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully.');
                
        } catch (\Exception $e) {
            if ($request->wantsJson() && $request->header('Accept') === 'application/json' && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting employee'
                ], 500);
            }
            
            return redirect()->route('employees.index')
                ->with('error', 'Failed to delete employee.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EmployeeContract;
use App\Models\Employee;
use App\Services\EmployeeContractService;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeContractController extends Controller
{
    use HasPermissions;

    protected $contractService;

    public function __construct(EmployeeContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of employee contracts
     */
    public function index(Request $request): Response
    {
        $this->authorizePermission('view_employee_contracts');

        $filters = $request->only(['search', 'type', 'is_active', 'salary_min', 'salary_max']);
        $perPage = $request->get('per_page', 15);

        $contracts = $this->contractService->getPaginatedContracts($filters, $perPage);
        $statistics = $this->contractService->getContractStatistics();

        return Inertia::render('Contract/Contract', [
            'contracts' => $contracts,
            'statistics' => $statistics,
            'filters' => $filters,
            'contractTypes' => $this->contractService->getContractTypes(),
        ]);
    }

    /**
     * Show the form for creating a new contract
     */
    public function create(): Response
    {
        $this->authorizePermission('create_employee_contracts');

        return Inertia::render('Contract/Create', [
            'contractTypes' => $this->contractService->getContractTypes(),
        ]);
    }

    /**
     * Store a newly created contract
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('create_employee_contracts');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:employee_contracts,name',
            'description' => 'nullable|string',
            'type' => 'required|in:permanent,contract,probation,internship',
            'base_salary' => 'nullable|numeric|min:0',
            'benefits' => 'nullable|array',
            'benefits.tunjangan_transport' => 'nullable|numeric|min:0',
            'benefits.tunjangan_makan' => 'nullable|numeric|min:0',
            'benefits.tunjangan_kesehatan' => 'nullable|numeric|min:0',
            'benefits.tunjangan_komunikasi' => 'nullable|numeric|min:0',
            'benefits.asuransi_kesehatan' => 'nullable|boolean',
            'benefits.jamsostek' => 'nullable|boolean',
            'benefits.cuti_tahunan' => 'nullable|integer|min:0',
            'benefits.bonus_kinerja' => 'nullable|boolean',
            'is_active' => 'boolean',
        ]);

        try {
            $contract = $this->contractService->createContract($validated);

            return response()->json([
                'message' => 'Employee contract created successfully',
                'contract' => $contract,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified contract
     */
    public function show(int $id): Response
    {
        $this->authorizePermission('view_employee_contracts');

        $contract = $this->contractService->getContractWithDetails($id);
        $report = $this->contractService->generateContractReport($id);

        return Inertia::render('Contract/Show', [
            'contract' => $contract,
            'report' => $report,
        ]);
    }

    /**
     * Show the form for editing the specified contract
     */
    public function edit(int $id): Response
    {
        $this->authorizePermission('edit_employee_contracts');

        $contract = EmployeeContract::findOrFail($id);

        return Inertia::render('Contract/Edit', [
            'contract' => $contract,
            'contractTypes' => $this->contractService->getContractTypes(),
        ]);
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('edit_employee_contracts');

        $contract = EmployeeContract::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:employee_contracts,name,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:permanent,contract,probation,internship',
            'base_salary' => 'nullable|numeric|min:0',
            'benefits' => 'nullable|array',
            'benefits.tunjangan_transport' => 'nullable|numeric|min:0',
            'benefits.tunjangan_makan' => 'nullable|numeric|min:0',
            'benefits.tunjangan_kesehatan' => 'nullable|numeric|min:0',
            'benefits.tunjangan_komunikasi' => 'nullable|numeric|min:0',
            'benefits.asuransi_kesehatan' => 'nullable|boolean',
            'benefits.jamsostek' => 'nullable|boolean',
            'benefits.cuti_tahunan' => 'nullable|integer|min:0',
            'benefits.bonus_kinerja' => 'nullable|boolean',
            'is_active' => 'boolean',
        ]);

        try {
            $contract = $this->contractService->updateContract($contract, $validated);

            return response()->json([
                'message' => 'Employee contract updated successfully',
                'contract' => $contract,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified contract
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('delete_employee_contracts');

        $contract = EmployeeContract::findOrFail($id);

        try {
            $this->contractService->deleteContract($contract);

            return response()->json([
                'message' => 'Employee contract deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get employees for a specific contract
     */
    public function employees(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('view_employee_contracts');

        $filters = $request->only(['search', 'status', 'department']);
        $perPage = $request->get('per_page', 15);

        $employees = $this->contractService->getContractEmployees($id, $filters, $perPage);

        return response()->json($employees);
    }

    /**
     * Assign employees to contract
     */
    public function assignEmployees(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('edit_employee_contracts');

        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        try {
            $updated = $this->contractService->assignEmployeesToContract($id, $validated['employee_ids']);

            return response()->json([
                'message' => "{$updated} employees assigned to contract successfully",
                'updated_count' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign employees to contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove employees from contract
     */
    public function removeEmployees(Request $request): JsonResponse
    {
        $this->authorizePermission('edit_employee_contracts');

        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        try {
            $updated = $this->contractService->removeEmployeesFromContract($validated['employee_ids']);

            return response()->json([
                'message' => "{$updated} employees removed from contract successfully",
                'updated_count' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove employees from contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle contract active status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $this->authorizePermission('edit_employee_contracts');

        $contract = EmployeeContract::findOrFail($id);

        try {
            $contract = $this->contractService->toggleContractStatus($contract);

            return response()->json([
                'message' => 'Contract status updated successfully',
                'contract' => $contract,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update contract status',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Duplicate contract
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('create_employee_contracts');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:employee_contracts,name',
        ]);

        $contract = EmployeeContract::findOrFail($id);

        try {
            $newContract = $this->contractService->duplicateContract($contract, $validated['name']);

            return response()->json([
                'message' => 'Contract duplicated successfully',
                'contract' => $newContract,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to duplicate contract',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get contract statistics
     */
    public function statistics(): JsonResponse
    {
        $this->authorizePermission('view_employee_contracts');

        $statistics = $this->contractService->getContractStatistics();

        return response()->json($statistics);
    }

    /**
     * Export contract report
     */
    public function exportReport(int $id): JsonResponse
    {
        $this->authorizePermission('view_employee_contracts');

        try {
            $report = $this->contractService->generateContractReport($id);

            return response()->json([
                'message' => 'Report generated successfully',
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate report',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available employees for assignment
     */
    public function availableEmployees(Request $request): JsonResponse
    {
        $this->authorizePermission('view_employee_contracts');

        $search = $request->get('search');
        $contractId = $request->get('contract_id');

        $query = Employee::select('id', 'employee_id', 'name', 'position', 'department', 'status')
            ->where('status', 'active');

        // Exclude employees already assigned to the specific contract
        if ($contractId) {
            $query->where(function ($q) use ($contractId) {
                $q->whereNull('employee_contract_id')
                  ->orWhere('employee_contract_id', '!=', $contractId);
            });
        } else {
            $query->whereNull('employee_contract_id');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('employee_id', 'LIKE', '%' . $search . '%');
            });
        }

        $employees = $query->orderBy('name')->limit(50)->get();

        return response()->json($employees);
    }
}

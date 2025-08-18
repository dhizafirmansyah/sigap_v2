<?php

namespace App\Services;

use App\Models\EmployeeContract;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeContractService
{
    /**
     * Get paginated employee contracts with filters
     */
    public function getPaginatedContracts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeContract::query()
            ->withCount('employees')
            ->orderBy('created_at', 'desc');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['salary_min'])) {
            $query->where('base_salary', '>=', $filters['salary_min']);
        }

        if (!empty($filters['salary_max'])) {
            $query->where('base_salary', '<=', $filters['salary_max']);
        }
    }

    /**
     * Create new employee contract
     */
    public function createContract(array $data): EmployeeContract
    {
        return DB::transaction(function () use ($data) {
            // Prepare benefits data
            if (!empty($data['benefits'])) {
                $data['benefits'] = $this->prepareBenefitsData($data['benefits']);
            }

            $contract = EmployeeContract::create($data);

            return $contract->load('employees');
        });
    }

    /**
     * Update employee contract
     */
    public function updateContract(EmployeeContract $contract, array $data): EmployeeContract
    {
        return DB::transaction(function () use ($contract, $data) {
            // Prepare benefits data
            if (!empty($data['benefits'])) {
                $data['benefits'] = $this->prepareBenefitsData($data['benefits']);
            }

            $contract->update($data);

            return $contract->fresh(['employees']);
        });
    }

    /**
     * Delete employee contract
     */
    public function deleteContract(EmployeeContract $contract): bool
    {
        return DB::transaction(function () use ($contract) {
            // Check if contract is being used by employees
            if ($contract->employees()->exists()) {
                throw new \Exception('Cannot delete contract that is currently assigned to employees. Please reassign employees first.');
            }

            return $contract->delete();
        });
    }

    /**
     * Get contract with detailed information
     */
    public function getContractWithDetails(int $contractId): EmployeeContract
    {
        return EmployeeContract::with([
            'employees' => function ($query) {
                $query->select('id', 'employee_id', 'name', 'position', 'department', 'status', 'employee_contract_id')
                      ->orderBy('name');
            }
        ])->findOrFail($contractId);
    }

    /**
     * Get contract statistics
     */
    public function getContractStatistics(): array
    {
        $totalContracts = EmployeeContract::count();
        $activeContracts = EmployeeContract::where('is_active', true)->count();
        $inactiveContracts = EmployeeContract::where('is_active', false)->count();

        // Contract type distribution
        $contractTypes = EmployeeContract::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Employees per contract type
        $employeesByType = Employee::select('employee_contracts.type', DB::raw('count(employees.id) as count'))
            ->join('employee_contracts', 'employees.employee_contract_id', '=', 'employee_contracts.id')
            ->groupBy('employee_contracts.type')
            ->pluck('count', 'type')
            ->toArray();

        // Average salary by contract type
        $avgSalaryByType = EmployeeContract::select('type', DB::raw('AVG(base_salary) as avg_salary'))
            ->groupBy('type')
            ->pluck('avg_salary', 'type')
            ->toArray();

        return [
            'total_contracts' => $totalContracts,
            'active_contracts' => $activeContracts,
            'inactive_contracts' => $inactiveContracts,
            'contract_types' => $contractTypes,
            'employees_by_type' => $employeesByType,
            'avg_salary_by_type' => $avgSalaryByType,
        ];
    }

    /**
     * Get employees assigned to a specific contract
     */
    public function getContractEmployees(int $contractId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::where('employee_contract_id', $contractId)
            ->with(['location'])
            ->orderBy('name');

        // Apply employee filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('employee_id', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Assign employees to contract
     */
    public function assignEmployeesToContract(int $contractId, array $employeeIds): int
    {
        return DB::transaction(function () use ($contractId, $employeeIds) {
            $contract = EmployeeContract::findOrFail($contractId);
            
            if (!$contract->is_active) {
                throw new \Exception('Cannot assign employees to inactive contract.');
            }

            $updated = Employee::whereIn('id', $employeeIds)
                ->update(['employee_contract_id' => $contractId]);

            return $updated;
        });
    }

    /**
     * Remove employees from contract
     */
    public function removeEmployeesFromContract(array $employeeIds): int
    {
        return Employee::whereIn('id', $employeeIds)
            ->update(['employee_contract_id' => null]);
    }

    /**
     * Toggle contract active status
     */
    public function toggleContractStatus(EmployeeContract $contract): EmployeeContract
    {
        return DB::transaction(function () use ($contract) {
            $contract->update(['is_active' => !$contract->is_active]);
            
            // If deactivating and has employees, warn about impact
            if (!$contract->is_active && $contract->employees()->exists()) {
                // Log this action or notify relevant parties
                \Log::info("Contract {$contract->name} deactivated with {$contract->employees()->count()} assigned employees");
            }

            return $contract->fresh();
        });
    }

    /**
     * Duplicate contract with new name
     */
    public function duplicateContract(EmployeeContract $contract, string $newName): EmployeeContract
    {
        return DB::transaction(function () use ($contract, $newName) {
            $newContract = $contract->replicate();
            $newContract->name = $newName;
            $newContract->is_active = false; // Start as inactive for review
            $newContract->save();

            return $newContract;
        });
    }

    /**
     * Get contracts summary for dashboard
     */
    public function getContractsSummary(): array
    {
        $contracts = EmployeeContract::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->limit(5)
            ->get();

        $recentContracts = EmployeeContract::orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return [
            'top_contracts' => $contracts,
            'recent_contracts' => $recentContracts,
        ];
    }

    /**
     * Prepare benefits data for storage
     */
    private function prepareBenefitsData(array $benefits): array
    {
        $prepared = [];
        
        foreach ($benefits as $key => $value) {
            if ($value !== null && $value !== '') {
                $prepared[$key] = $value;
            }
        }

        return $prepared;
    }

    /**
     * Get available contract types
     */
    public function getContractTypes(): array
    {
        return [
            'permanent' => 'Permanent',
            'contract' => 'Contract',
            'probation' => 'Probation',
            'internship' => 'Internship'
        ];
    }

    /**
     * Generate contract report
     */
    public function generateContractReport(int $contractId): array
    {
        $contract = $this->getContractWithDetails($contractId);
        
        $employees = $contract->employees;
        $totalEmployees = $employees->count();
        $activeEmployees = $employees->where('status', 'active')->count();
        
        $departmentDistribution = $employees->groupBy('department')->map->count();
        $statusDistribution = $employees->groupBy('status')->map->count();

        return [
            'contract' => $contract,
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'department_distribution' => $departmentDistribution,
            'status_distribution' => $statusDistribution,
        ];
    }
}

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\EmployeeContractController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::delete('users/{user}/remove-role', [UserController::class, 'removeRole'])->name('users.remove-role');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Reports routes (for demo purposes) 
    Route::get('/reports', function() {
        return Inertia::render('Reports/Index');
    })->name('reports.index');
    
    // Settings routes (for demo purposes)
    Route::get('/settings', function() {
        return Inertia::render('Settings/Index'); 
    })->name('settings.index');
    
    // Employee routes
    Route::resource('employees', EmployeeController::class);
    Route::get('employees-search', [EmployeeController::class, 'search'])->name('employees.search');
    
    // Brand routes
    Route::resource('brands', BrandController::class);
    Route::get('brands-search', [BrandController::class, 'search'])->name('brands.search');
    
    // Location routes
    Route::resource('locations', LocationController::class);
    Route::get('locations-search', [LocationController::class, 'search'])->name('locations.search');
    
    // Shift management routes
    Route::resource('shifts', ShiftController::class);
    Route::post('shifts/{shift}/duplicate', [ShiftController::class, 'duplicate'])->name('shifts.duplicate');
    Route::patch('shifts/{shift}/toggle-status', [ShiftController::class, 'toggleStatus'])->name('shifts.toggle-status');
    Route::post('shifts/{shift}/assign-employees', [ShiftController::class, 'assignEmployees'])->name('shifts.assign-employees');
    Route::delete('shifts/{shift}/remove-employee/{employee}', [ShiftController::class, 'removeEmployee'])->name('shifts.remove-employee');
    Route::get('shifts/{shift}/available-employees', [ShiftController::class, 'availableEmployees'])->name('shifts.available-employees');
    Route::post('shifts/{shift}/check-conflicts', [ShiftController::class, 'checkConflicts'])->name('shifts.check-conflicts');
    Route::get('shift-schedule', [ShiftController::class, 'schedule'])->name('shifts.schedule');
    
    // Presence management routes
    Route::resource('presences', PresenceController::class);
    Route::post('presences/check-in', [PresenceController::class, 'checkIn'])->name('presences.check-in');
    Route::post('presences/{presence}/check-out', [PresenceController::class, 'checkOut'])->name('presences.check-out');
    Route::get('presences/reports/daily', [PresenceController::class, 'dailyReport'])->name('presences.daily-report');
    Route::get('presences/reports/monthly', [PresenceController::class, 'monthlyReport'])->name('presences.monthly-report');
    
    // Employee Contract management routes
    Route::resource('employee-contracts', EmployeeContractController::class);
    Route::get('employee-contracts/{id}/employees', [EmployeeContractController::class, 'employees'])->name('employee-contracts.employees');
    Route::post('employee-contracts/{id}/assign-employees', [EmployeeContractController::class, 'assignEmployees'])->name('employee-contracts.assign-employees');
    Route::post('employee-contracts/remove-employees', [EmployeeContractController::class, 'removeEmployees'])->name('employee-contracts.remove-employees');
    Route::post('employee-contracts/{id}/toggle-status', [EmployeeContractController::class, 'toggleStatus'])->name('employee-contracts.toggle-status');
    Route::post('employee-contracts/{id}/duplicate', [EmployeeContractController::class, 'duplicate'])->name('employee-contracts.duplicate');
    Route::get('employee-contracts-statistics', [EmployeeContractController::class, 'statistics'])->name('employee-contracts.statistics');
    Route::get('employee-contracts/{id}/export-report', [EmployeeContractController::class, 'exportReport'])->name('employee-contracts.export-report');
    Route::get('available-employees', [EmployeeContractController::class, 'availableEmployees'])->name('employee-contracts.available-employees');
    
    // Role management routes (protected by permission middleware)
    Route::middleware(['permission:view_users'])->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('roles-hierarchy', [RoleController::class, 'hierarchy'])->name('roles.hierarchy');
        Route::get('roles-statistics', [RoleController::class, 'statistics'])->name('roles.statistics');
    });
    
    Route::middleware(['permission:create_users'])->group(function () {
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::post('roles/{role}/duplicate', [RoleController::class, 'duplicate'])->name('roles.duplicate');
    });
    
    Route::middleware(['permission:edit_users'])->group(function () {
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('roles/{role}/assign-user', [RoleController::class, 'assignUser'])->name('roles.assign-user');
        Route::post('roles/{role}/remove-user', [RoleController::class, 'removeUser'])->name('roles.remove-user');
    });
    
    Route::middleware(['permission:delete_users'])->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});

// API test routes tanpa auth untuk testing fetch API
Route::prefix('api-test')->group(function () {
    Route::get('employees', function () {
        $employees = \App\Models\Employee::with(['location', 'pkwt', 'employeeContract'])
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $employees,
            'props' => [
                'employees' => \App\Http\Resources\EmployeeResource::collection($employees),
                'locations' => \App\Models\Location::where('is_active', true)->get(['id', 'name']),
            ]
        ]);
    });
    
    Route::get('employees-search', function () {
        $query = \App\Models\Employee::with(['location', 'pkwt', 'employeeContract']);
        
        if (request()->filled('q')) {
            $search = request()->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->limit(20)->get();
        
        return response()->json([
            'success' => true,
            'data' => \App\Http\Resources\EmployeeResource::collection($employees)
        ]);
    });
});

// Test page untuk API
Route::get('test-api', function () {
    return view('api-test');
});

require __DIR__.'/auth.php';

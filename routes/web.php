<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LocationController;
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
    return Inertia::render('Dashboard', [
        'auth' => [
            'user' => auth()->user()
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Users management routes (for demo purposes)
    Route::get('/users', function() {
        return Inertia::render('Users/Index');
    })->name('users.index');
    
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

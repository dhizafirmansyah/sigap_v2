<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-employees', function () {
    $employees = \App\Models\Employee::with(['location', 'pkwt'])->paginate(10);
    return response()->json([
        'employees' => \App\Http\Resources\EmployeeResource::collection($employees),
        'success' => true
    ]);
})->middleware('auth');

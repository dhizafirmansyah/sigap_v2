<?php

use App\Http\Controllers\ProfileController;
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
});

require __DIR__.'/auth.php';

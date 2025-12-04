<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('api.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Temporary dashboard until authors page is created
    Route::get('/authors', function () {
        return 'Login successful! Token stored. Authors page coming next.';
    })->name('authors.index');
});

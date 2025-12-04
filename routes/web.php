<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
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
    
    // Authors
    Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::get('/authors/{id}', [AuthorController::class, 'show'])->name('authors.show');
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy'])->name('authors.destroy');
    
    // Books (placeholder routes - will be implemented next)
    Route::get('/books/create', function () { return 'Add book form coming soon'; })->name('books.create');
    Route::delete('/books/{id}', function ($id) { return back(); })->name('books.destroy');
});

<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'not.suspended'])
    ->name('dashboard');

Route::middleware(['auth', 'not.suspended'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/student.php';
require __DIR__.'/instructor.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

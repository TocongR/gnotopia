<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::inertia('/dashboard', 'Dashboard/Student')->name('dashboard');
    });

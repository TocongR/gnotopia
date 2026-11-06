<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::inertia('/dashboard', 'Dashboard/Instructor')->name('dashboard');
    });

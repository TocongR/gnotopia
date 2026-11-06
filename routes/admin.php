<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::inertia('/dashboard', 'Dashboard/Admin')->name('dashboard');
    });

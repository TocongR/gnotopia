<?php

use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\CoursePublishController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::inertia('/dashboard', 'Dashboard/Instructor')->name('dashboard');

        Route::resource('courses', CourseController::class)->except('show');
        Route::post('courses/{course}/publish', [CoursePublishController::class, 'store'])
            ->name('courses.publish');
        Route::delete('courses/{course}/publish', [CoursePublishController::class, 'destroy'])
            ->name('courses.unpublish');
    });

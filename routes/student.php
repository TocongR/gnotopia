<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\LessonProgressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::post('courses/{course:slug}/enroll', [EnrollmentController::class, 'store'])
            ->name('courses.enroll');

        Route::get('learn/{course:slug}', [LearnController::class, 'show'])->name('learn.show');
        Route::get('learn/{course:slug}/lessons/{lesson}', [LearnController::class, 'lesson'])->name('learn.lesson');

        Route::post('lessons/{lesson}/complete', [LessonProgressController::class, 'store'])->name('lessons.complete');
        Route::delete('lessons/{lesson}/complete', [LessonProgressController::class, 'destroy'])->name('lessons.uncomplete');
    });

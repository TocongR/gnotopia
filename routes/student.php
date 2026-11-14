<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\LessonProgressController;
use App\Http\Controllers\Student\QuizAttemptController;
use App\Http\Controllers\Student\QuizController;
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

        Route::get('learn/{course:slug}/quizzes/{quiz}', [QuizController::class, 'show'])->name('learn.quiz');
        Route::post('learn/{course:slug}/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('quizzes.attempts.store');
        Route::get('learn/{course:slug}/quizzes/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'show'])
            ->name('quizzes.attempts.show');
    });

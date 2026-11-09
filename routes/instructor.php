<?php

use App\Http\Controllers\Instructor\CourseContentController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\CoursePublishController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Instructor\ReorderModuleItemsController;
use App\Http\Controllers\Instructor\ReorderModulesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::inertia('/dashboard', 'Dashboard/Instructor')->name('dashboard');

        // Courses
        Route::resource('courses', CourseController::class)->except('show');
        Route::post('courses/{course}/publish', [CoursePublishController::class, 'store'])
            ->name('courses.publish');
        Route::delete('courses/{course}/publish', [CoursePublishController::class, 'destroy'])
            ->name('courses.unpublish');

        // Course content: modules and lessons
        Route::get('courses/{course}/content', CourseContentController::class)->name('courses.content');
        Route::put('courses/{course}/modules/reorder', ReorderModulesController::class)->name('courses.modules.reorder');
        Route::resource('courses.modules', ModuleController::class)->only(['store', 'update', 'destroy'])->shallow();
        Route::put('modules/{module}/items/reorder', ReorderModuleItemsController::class)->name('modules.items.reorder');
        Route::resource('modules.lessons', LessonController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])->shallow();
    });

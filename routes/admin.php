<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseSuspensionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserSuspensionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->where(['course' => '[0-9]{1,18}'])
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/role', [UserRoleController::class, 'update'])->name('users.role');
        Route::post('users/{user}/suspension', [UserSuspensionController::class, 'store'])->name('users.suspend');
        Route::delete('users/{user}/suspension', [UserSuspensionController::class, 'destroy'])->name('users.unsuspend');

        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::post('courses/{course}/suspension', [CourseSuspensionController::class, 'store'])->name('courses.suspend');
        Route::delete('courses/{course}/suspension', [CourseSuspensionController::class, 'destroy'])->name('courses.restore');
    });

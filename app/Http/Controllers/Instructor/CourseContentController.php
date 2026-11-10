<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseContentController extends Controller
{
    public function __invoke(Course $course): Response
    {
        Gate::authorize('update', $course);

        $course->load(['modules.lessons', 'modules.quizzes']);

        return Inertia::render('Instructor/Courses/Content', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status->value,
            ],
            'modules' => $course->modules->map(fn (Module $module) => [
                'id' => $module->id,
                'title' => $module->title,
                'items' => $module->outline(),
            ])->values(),
        ]);
    }
}

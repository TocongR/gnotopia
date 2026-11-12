<?php

namespace App\Http\Controllers\Student;

use App\Actions\Learning\CompleteLesson;
use App\Actions\Learning\UncompleteLesson;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LessonProgressController extends Controller
{
    public function store(Request $request, Lesson $lesson, CompleteLesson $completeLesson): RedirectResponse
    {
        Gate::authorize('learn', $lesson->module->course);

        $completeLesson->handle($request->user(), $lesson);

        return back();
    }

    public function destroy(Request $request, Lesson $lesson, UncompleteLesson $uncompleteLesson): RedirectResponse
    {
        Gate::authorize('learn', $lesson->module->course);

        $uncompleteLesson->handle($request->user(), $lesson);

        return back();
    }
}

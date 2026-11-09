<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Content\SaveLesson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\SaveLessonRequest;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function create(Module $module): Response
    {
        Gate::authorize('update', $module->course);

        return Inertia::render('Instructor/Lessons/Create', [
            'course' => $module->course->only('id', 'title'),
            'module' => $module->only('id', 'title'),
        ]);
    }

    public function store(SaveLessonRequest $request, Module $module, SaveLesson $saveLesson): RedirectResponse
    {
        $saveLesson->handle($module, $request->validated());

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Lesson added.');
    }

    public function edit(Lesson $lesson): Response
    {
        $module = $lesson->module;

        Gate::authorize('update', $module->course);

        return Inertia::render('Instructor/Lessons/Edit', [
            'course' => $module->course->only('id', 'title'),
            'module' => $module->only('id', 'title'),
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->type->value,
                'video_url' => $lesson->video_url,
                'body' => $lesson->body,
            ],
        ]);
    }

    public function update(SaveLessonRequest $request, Lesson $lesson, SaveLesson $saveLesson): RedirectResponse
    {
        $module = $lesson->module;

        $saveLesson->handle($module, $request->validated(), $lesson);

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $module = $lesson->module;

        Gate::authorize('update', $module->course);

        $lesson->delete();

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Lesson deleted.');
    }
}

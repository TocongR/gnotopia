<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Content\SaveQuiz;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\SaveQuizRequest;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function create(Module $module): Response
    {
        Gate::authorize('update', $module->course);

        return Inertia::render('Instructor/Quizzes/Create', [
            'course' => $module->course->only('id', 'title'),
            'module' => $module->only('id', 'title'),
        ]);
    }

    public function store(SaveQuizRequest $request, Module $module, SaveQuiz $saveQuiz): RedirectResponse
    {
        $saveQuiz->handle($module, $request->validated());

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Quiz added.');
    }

    public function edit(Quiz $quiz): Response
    {
        $module = $quiz->module;

        Gate::authorize('update', $module->course);

        $quiz->load('questions.options');

        return Inertia::render('Instructor/Quizzes/Edit', [
            'course' => $module->course->only('id', 'title'),
            'module' => $module->only('id', 'title'),
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'pass_percent' => $quiz->pass_percent,
                'questions' => $quiz->questions->map(fn ($question) => [
                    'id' => $question->id,
                    'body' => $question->body,
                    'options' => $question->options->map(fn ($option) => [
                        'id' => $option->id,
                        'body' => $option->body,
                        'is_correct' => $option->is_correct,
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    public function update(SaveQuizRequest $request, Quiz $quiz, SaveQuiz $saveQuiz): RedirectResponse
    {
        $module = $quiz->module;

        $saveQuiz->handle($module, $request->validated(), $quiz);

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $module = $quiz->module;

        Gate::authorize('update', $module->course);

        $quiz->delete();

        return redirect()
            ->route('instructor.courses.content', $module->course_id)
            ->with('success', 'Quiz deleted.');
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\LearningPath;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function show(
        Request $request,
        Course $course,
        Quiz $quiz,
        LearningPath $path,
        ProgressService $progress,
    ): Response {
        Gate::authorize('learn', $course);

        // The quiz in the URL must belong to the course in the URL.
        abort_unless($quiz->module->course_id === $course->id, 404);

        $user = $request->user();

        $path->load($course);
        $steps = $path->steps($course);
        $done = $path->completedKeys($user, $steps);

        // Quizzes without questions are not in the path, so they 404 here.
        $position = $steps->search(fn (array $step) => $step['key'] === 'quiz-'.$quiz->id);
        abort_if($position === false, 404);

        $quiz->load('questions.options');

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return Inertia::render('Learn/Quiz', [
            'course' => $course->only('id', 'title', 'slug'),
            'modules' => $path->sidebar($course, $done),
            'progress' => $progress->summaries($user, collect([$course->id]))[$course->id],
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'pass_percent' => $quiz->pass_percent,
                'passed' => in_array('quiz-'.$quiz->id, $done, true),
                // Only ids and text. is_correct never leaves the server before submitting.
                'questions' => $quiz->questions->map(fn ($question) => [
                    'id' => $question->id,
                    'body' => $question->body,
                    'options' => $question->options->map(fn ($option) => [
                        'id' => $option->id,
                        'body' => $option->body,
                    ])->values(),
                ])->values(),
            ],
            'attempts' => $attempts->map(fn (QuizAttempt $attempt) => [
                'id' => $attempt->id,
                'score_percent' => $attempt->score_percent,
                'passed' => $attempt->passed,
                'taken_at' => $attempt->created_at->diffForHumans(),
                'url' => route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]),
            ])->values(),
            'prev' => $path->neighbour($course, $steps->get($position - 1)),
            'next' => $path->neighbour($course, $steps->get($position + 1)),
        ]);
    }
}

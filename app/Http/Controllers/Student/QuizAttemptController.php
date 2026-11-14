<?php

namespace App\Http\Controllers\Student;

use App\Actions\Learning\SubmitQuizAttempt;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitQuizAttemptRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\LearningPath;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    public function store(
        SubmitQuizAttemptRequest $request,
        Course $course,
        Quiz $quiz,
        SubmitQuizAttempt $submitQuizAttempt,
    ): RedirectResponse {
        abort_unless($quiz->module->course_id === $course->id, 404);

        // Only the answers are read. Anything else in the request (a score, a "passed" flag) is ignored.
        $attempt = $submitQuizAttempt->handle($request->user(), $quiz, $request->validated('answers'));

        return redirect()->route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]);
    }

    public function show(
        Request $request,
        Course $course,
        Quiz $quiz,
        QuizAttempt $attempt,
        LearningPath $path,
        ProgressService $progress,
    ): Response {
        Gate::authorize('learn', $course);

        $user = $request->user();

        abort_unless($quiz->module->course_id === $course->id, 404);
        // Students can only ever open their own attempts, on the quiz named in the URL.
        abort_unless((int) $attempt->user_id === (int) $user->id && (int) $attempt->quiz_id === (int) $quiz->id, 404);

        $path->load($course);
        $steps = $path->steps($course);
        $done = $path->completedKeys($user, $steps);

        $position = $steps->search(fn (array $step) => $step['key'] === 'quiz-'.$quiz->id);
        abort_if($position === false, 404);

        $review = collect($attempt->answers);

        return Inertia::render('Learn/QuizResult', [
            'course' => $course->only('id', 'title', 'slug'),
            'modules' => $path->sidebar($course, $done),
            'progress' => $progress->summaries($user, collect([$course->id]))[$course->id],
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'pass_percent' => $quiz->pass_percent,
                'url' => route('student.learn.quiz', [$course->slug, $quiz->id]),
            ],
            'attempt' => [
                'id' => $attempt->id,
                'score_percent' => $attempt->score_percent,
                'passed' => $attempt->passed,
                'correct_count' => $review->where('is_correct', true)->count(),
                'total' => $review->count(),
                'taken_at' => $attempt->created_at->toDayDateTimeString(),
            ],
            'review' => $review->values(),
            'prev' => $path->neighbour($course, $steps->get($position - 1)),
            'next' => $path->neighbour($course, $steps->get($position + 1)),
        ]);
    }
}

<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * An enrolled student plus a published course with $lessons lessons and one quiz after them.
 *
 * @return array{0: User, 1: Course, 2: Quiz, 3: Collection}
 */
function quizCourse(int $lessons = 1, int $questions = 3, int $passPercent = 70): array
{
    [$course, $lessonModels] = learningCourse($lessons);
    $quiz = quizWithQuestions($course->modules()->first(), $questions, ['pass_percent' => $passPercent]);

    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();

    return [$student, $course, $quiz, $lessonModels];
}

function submitAnswers(User $student, $course, Quiz $quiz, array $answers, array $extra = [])
{
    return test()->actingAs($student)->post(
        route('student.quizzes.attempts.store', [$course->slug, $quiz->id]),
        ['answers' => $answers] + $extra,
    );
}

// ---------------------------------------------------------------- the quiz page

it('shows the questions without revealing the correct answers', function () {
    [$student, $course, $quiz] = quizCourse();

    $this->actingAs($student)
        ->get(route('student.learn.quiz', [$course->slug, $quiz->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Learn/Quiz')
            ->where('quiz.id', $quiz->id)
            ->has('quiz.questions', 3)
            ->has('quiz.questions.0.options', 3)
            ->missing('quiz.questions.0.options.0.is_correct')
            ->has('attempts', 0));
});

it('blocks students who are not enrolled', function () {
    [, $course, $quiz] = quizCourse();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('student.learn.quiz', [$course->slug, $quiz->id]))
        ->assertForbidden();

    submitAnswers($outsider, $course, $quiz, quizAnswers($quiz))->assertForbidden();

    expect(QuizAttempt::count())->toBe(0);
});

it('blocks instructors and admins', function (string $state) {
    [, $course, $quiz] = quizCourse();

    $this->actingAs(User::factory()->{$state}()->create())
        ->get(route('student.learn.quiz', [$course->slug, $quiz->id]))
        ->assertForbidden();
})->with(['instructor', 'admin']);

it('returns 404 for a quiz that belongs to another course', function () {
    [$student, $course] = quizCourse();
    [, , $otherQuiz] = quizCourse();

    $this->actingAs($student)
        ->get(route('student.learn.quiz', [$course->slug, $otherQuiz->id]))
        ->assertNotFound();
});

it('hides quizzes that have no questions', function () {
    [$student, $course] = quizCourse();
    $empty = Quiz::factory()->for($course->modules()->first())->create(['position' => 20]);

    $this->actingAs($student)
        ->get(route('student.learn.quiz', [$course->slug, $empty->id]))
        ->assertNotFound();

    submitAnswers($student, $course, $empty, [1 => 1])->assertSessionHasErrors('answers');

    expect(QuizAttempt::count())->toBe(0);
});

it('locks quizzes of a suspended course', function () {
    [$student, $course, $quiz] = quizCourse();
    $course->status = CourseStatus::Suspended;
    $course->save();

    $this->actingAs($student)
        ->get(route('student.learn.quiz', [$course->slug, $quiz->id]))
        ->assertForbidden();

    submitAnswers($student, $course, $quiz, quizAnswers($quiz))->assertForbidden();
});

// ---------------------------------------------------------------- grading

it('grades on the server and redirects to the result', function () {
    [$student, $course, $quiz] = quizCourse();

    $response = submitAnswers($student, $course, $quiz, quizAnswers($quiz));

    $attempt = QuizAttempt::first();

    $response->assertRedirect(route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]));

    expect($attempt->user_id)->toEqual($student->id)
        ->and($attempt->score_percent)->toBe(100)
        ->and($attempt->passed)->toBeTrue()
        ->and($attempt->answers)->toHaveCount(3);
});

it('scores and applies the passing mark', function (int $questions, int $correct, int $passPercent, int $score, bool $passed) {
    [$student, $course, $quiz] = quizCourse(1, $questions, $passPercent);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz, $correct));

    $attempt = QuizAttempt::first();

    expect($attempt->score_percent)->toBe($score)
        ->and($attempt->passed)->toBe($passed);
})->with([
    'all right' => [3, 3, 70, 100, true],
    'none right' => [3, 0, 70, 0, false],
    'two of three, mark 70' => [3, 2, 70, 66, false],
    'two of three, mark 66' => [3, 2, 66, 66, true],
    'two of three, mark 67' => [3, 2, 67, 66, false],
    'exactly at the mark' => [4, 3, 75, 75, true],
]);

it('ignores a score or passed flag sent by the browser', function () {
    [$student, $course, $quiz] = quizCourse();

    submitAnswers($student, $course, $quiz, quizAnswers($quiz, 0), [
        'score_percent' => 100,
        'passed' => true,
    ]);

    $attempt = QuizAttempt::first();

    expect($attempt->score_percent)->toBe(0)
        ->and($attempt->passed)->toBeFalse();
});

it('requires every question to be answered', function () {
    [$student, $course, $quiz] = quizCourse();

    $answers = quizAnswers($quiz);
    array_pop($answers);

    submitAnswers($student, $course, $quiz, $answers)->assertSessionHasErrors('answers');

    expect(QuizAttempt::count())->toBe(0);
});

it('rejects an option that belongs to a different question', function () {
    [$student, $course, $quiz] = quizCourse();

    $answers = quizAnswers($quiz);
    $answers[$quiz->questions->first()->id] = $quiz->questions->last()->options->first()->id;

    submitAnswers($student, $course, $quiz, $answers)->assertSessionHasErrors('answers');

    expect(QuizAttempt::count())->toBe(0);
});

it('rejects an option from a different quiz', function () {
    [$student, $course, $quiz] = quizCourse();
    [, , $otherQuiz] = quizCourse();

    $answers = quizAnswers($quiz);
    $answers[$quiz->questions->first()->id] = $otherQuiz->questions->first()->options->first()->id;

    submitAnswers($student, $course, $quiz, $answers)->assertSessionHasErrors('answers');

    expect(QuizAttempt::count())->toBe(0);
});

it('lets a student retake a quiz and lists the attempts', function () {
    [$student, $course, $quiz] = quizCourse();

    submitAnswers($student, $course, $quiz, quizAnswers($quiz, 0));
    submitAnswers($student, $course, $quiz, quizAnswers($quiz));

    expect(QuizAttempt::count())->toBe(2);

    $this->actingAs($student)
        ->get(route('student.learn.quiz', [$course->slug, $quiz->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('attempts', 2)
            ->where('quiz.passed', true));
});

// ---------------------------------------------------------------- the result page

it('shows a review including the correct answers', function () {
    [$student, $course, $quiz] = quizCourse(1, 2);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz, 1));
    $attempt = QuizAttempt::first();

    $this->actingAs($student)
        ->get(route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Learn/QuizResult')
            ->where('attempt.score_percent', 50)
            ->where('attempt.correct_count', 1)
            ->where('attempt.total', 2)
            ->where('attempt.passed', false)
            ->has('review', 2)
            ->where('review.0.is_correct', true)
            ->where('review.1.is_correct', false)
            ->where('review.1.correct', fn ($text) => is_string($text) && $text !== ''));
});

it('keeps the review exactly as the student saw it after the quiz is edited', function () {
    [$student, $course, $quiz] = quizCourse(1, 3);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz));
    $attempt = QuizAttempt::first();

    $quiz->questions->first()->update(['body' => 'Changed afterwards']);
    $quiz->questions->last()->delete();

    $this->actingAs($student)
        ->get(route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('review', 3)
            ->where('review.0.question', fn ($text) => $text !== 'Changed afterwards'));
});

it('only lets a student open their own attempts', function () {
    [$student, $course, $quiz] = quizCourse();

    submitAnswers($student, $course, $quiz, quizAnswers($quiz));
    $attempt = QuizAttempt::first();

    $classmate = User::factory()->create();
    Enrollment::factory()->for($classmate)->for($course)->create();

    $this->actingAs($classmate)
        ->get(route('student.quizzes.attempts.show', [$course->slug, $quiz->id, $attempt->id]))
        ->assertNotFound();
});

it('returns 404 when the attempt does not belong to the quiz in the url', function () {
    [$student, $course, $quiz] = quizCourse();
    $secondQuiz = quizWithQuestions($course->modules()->first(), 1, ['position' => 11]);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz));
    $attempt = QuizAttempt::first();

    $this->actingAs($student)
        ->get(route('student.quizzes.attempts.show', [$course->slug, $secondQuiz->id, $attempt->id]))
        ->assertNotFound();
});

// ---------------------------------------------------------------- the learning path

it('counts passed quizzes as progress but not failed ones', function () {
    [$student, $course, $quiz, $lessons] = quizCourse(1, 2);
    $summary = fn () => app(ProgressService::class)->summaries($student, collect([$course->id]))[$course->id];

    LessonProgress::factory()->for($student)->for($lessons[0])->create();
    expect($summary())->toMatchArray(['done' => 1, 'total' => 2, 'percent' => 50]);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz, 0));
    expect($summary()['done'])->toBe(1);

    submitAnswers($student, $course, $quiz, quizAnswers($quiz));
    expect($summary())->toMatchArray(['done' => 2, 'total' => 2, 'percent' => 100]);
});

it('does not count quizzes without questions toward progress', function () {
    [$student, $course] = quizCourse(1, 2);
    Quiz::factory()->for($course->modules()->first())->create(['position' => 20]);

    $summary = app(ProgressService::class)->summaries($student, collect([$course->id]))[$course->id];

    expect($summary['total'])->toBe(2); // 1 lesson + 1 quiz that has questions
});

it('links lessons and quizzes into one path', function () {
    [$student, $course, $quiz, $lessons] = quizCourse(2, 2);
    $quizUrl = route('student.learn.quiz', [$course->slug, $quiz->id]);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[1]->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('next.type', 'quiz')
            ->where('next.id', $quiz->id)
            ->where('next.url', $quizUrl));

    $this->actingAs($student)
        ->get($quizUrl)
        ->assertInertia(fn (Assert $page) => $page
            ->where('prev.type', 'lesson')
            ->where('prev.id', $lessons[1]->id)
            ->where('next', null));
});

it('marks a passed quiz as completed in the sidebar', function () {
    [$student, $course, $quiz, $lessons] = quizCourse(1, 2);
    $lessonUrl = route('student.learn.lesson', [$course->slug, $lessons[0]->id]);

    $this->actingAs($student)->get($lessonUrl)
        ->assertInertia(fn (Assert $page) => $page->where('modules.0.items.1.completed', false));

    submitAnswers($student, $course, $quiz, quizAnswers($quiz));

    $this->actingAs($student)->get($lessonUrl)
        ->assertInertia(fn (Assert $page) => $page->where('modules.0.items.1.completed', true));
});

it('resumes at the quiz once the lessons are done', function () {
    [$student, $course, $quiz, $lessons] = quizCourse(2, 2);

    foreach ($lessons as $lesson) {
        LessonProgress::factory()->for($student)->for($lesson)->create();
    }

    $this->actingAs($student)
        ->get(route('student.learn.show', $course->slug))
        ->assertRedirect(route('student.learn.quiz', [$course->slug, $quiz->id]));
});

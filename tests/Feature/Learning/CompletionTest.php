<?php

use App\Actions\Learning\CheckCourseCompletion;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * A student enrolled in a course with $lessons lessons and, optionally, one quiz.
 *
 * @return array{0: User, 1: Course, 2: Collection, 3: ?Quiz, 4: Enrollment}
 */
function completionSetup(int $lessons = 2, bool $withQuiz = false): array
{
    [$course, $lessonModels] = learningCourse($lessons);
    $quiz = $withQuiz ? quizWithQuestions($course->modules()->first(), 2) : null;

    $student = User::factory()->create();
    $enrollment = Enrollment::factory()->for($student)->for($course)->create();

    return [$student, $course, $lessonModels, $quiz, $enrollment];
}

function finishLesson(User $student, Lesson $lesson): void
{
    test()->actingAs($student)->post(route('student.lessons.complete', $lesson));
}

function undoLesson(User $student, Lesson $lesson): void
{
    test()->actingAs($student)->delete(route('student.lessons.uncomplete', $lesson));
}

function attemptQuiz(User $student, $course, Quiz $quiz, array $answers): void
{
    test()->actingAs($student)->post(
        route('student.quizzes.attempts.store', [$course->slug, $quiz->id]),
        ['answers' => $answers],
    );
}

it('completes the enrollment when the last lesson is finished', function () {
    [$student, , $lessons, , $enrollment] = completionSetup(2);

    finishLesson($student, $lessons[0]);
    expect($enrollment->fresh()->completed_at)->toBeNull();

    finishLesson($student, $lessons[1]);
    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('needs the quiz to be passed, not just attempted', function () {
    [$student, $course, $lessons, $quiz, $enrollment] = completionSetup(1, true);

    finishLesson($student, $lessons[0]);
    expect($enrollment->fresh()->completed_at)->toBeNull();

    attemptQuiz($student, $course, $quiz, quizAnswers($quiz, 0));
    expect($enrollment->fresh()->completed_at)->toBeNull();

    attemptQuiz($student, $course, $quiz, quizAnswers($quiz));
    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('completes when the lesson is the last step and the quiz was passed first', function () {
    [$student, $course, $lessons, $quiz, $enrollment] = completionSetup(1, true);

    attemptQuiz($student, $course, $quiz, quizAnswers($quiz));
    expect($enrollment->fresh()->completed_at)->toBeNull();

    finishLesson($student, $lessons[0]);
    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('ignores quizzes that have no questions', function () {
    [$student, $course, $lessons, , $enrollment] = completionSetup(1);
    Quiz::factory()->for($course->modules()->first())->create(['position' => 20]);

    finishLesson($student, $lessons[0]);

    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('does not complete other students enrollments', function () {
    [$student, $course, $lessons] = completionSetup(1);
    $classmate = User::factory()->create();
    $other = Enrollment::factory()->for($classmate)->for($course)->create();

    finishLesson($student, $lessons[0]);

    expect($other->fresh()->completed_at)->toBeNull();
});

it('keeps the original completion time when a lesson is submitted again', function () {
    [$student, , $lessons, , $enrollment] = completionSetup(2);

    foreach ($lessons as $lesson) {
        finishLesson($student, $lesson);
    }

    $first = $enrollment->fresh()->completed_at;

    $this->travel(2)->hours();
    finishLesson($student, $lessons[1]);

    expect($enrollment->fresh()->completed_at->equalTo($first))->toBeTrue();
});

it('takes completion back when a lesson is marked incomplete, and grants it again later', function () {
    [$student, , $lessons, , $enrollment] = completionSetup(2);

    foreach ($lessons as $lesson) {
        finishLesson($student, $lesson);
    }

    expect($enrollment->fresh()->completed_at)->not->toBeNull();

    undoLesson($student, $lessons[0]);
    expect($enrollment->fresh()->completed_at)->toBeNull();

    finishLesson($student, $lessons[0]);
    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('stays completed when the instructor adds a lesson afterwards', function () {
    [$student, $course, $lessons, , $enrollment] = completionSetup(2);

    foreach ($lessons as $lesson) {
        finishLesson($student, $lesson);
    }

    Lesson::factory()->for($course->modules()->first())->create(['position' => 5]);

    // Any student action re-checks completion; it must not revoke it.
    finishLesson($student, $lessons[0]);

    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('never completes a course that has no content', function () {
    [$course] = learningCourse(0);
    $student = User::factory()->create();
    $enrollment = Enrollment::factory()->for($student)->for($course)->create();

    $result = app(CheckCourseCompletion::class)->handle($student, $course->id);

    expect($result)->toBeFalse()
        ->and($enrollment->fresh()->completed_at)->toBeNull();
});

it('does nothing for a student who is not enrolled', function () {
    [$course] = learningCourse(1);

    $result = app(CheckCourseCompletion::class)->handle(User::factory()->create(), $course->id);

    expect($result)->toBeFalse()
        ->and(Enrollment::count())->toBe(0);
});

it('backfills finished enrollments with the sync command', function () {
    [$student, , $lessons, , $enrollment] = completionSetup(2);

    // Progress recorded directly, so no completion check ran.
    foreach ($lessons as $lesson) {
        LessonProgress::factory()->for($student)->for($lesson)->create();
    }

    expect($enrollment->fresh()->completed_at)->toBeNull();

    $this->artisan('completions:sync')
        ->expectsOutputToContain('Marked 1 enrollment')
        ->assertSuccessful();

    expect($enrollment->fresh()->completed_at)->not->toBeNull();
});

it('flags completed courses on the student dashboard', function () {
    [$doneCourse] = learningCourse(1);
    [$openCourse] = learningCourse(1);
    $student = User::factory()->create();

    Enrollment::factory()->for($student)->for($doneCourse)->create(['completed_at' => now()]);
    Enrollment::factory()->for($student)->for($openCourse)->create();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses', 2)
            ->where('courses', function ($courses) use ($doneCourse, $openCourse) {
                $byId = collect($courses)->keyBy('id');

                return $byId[$doneCourse->id]['completed'] === true
                    && is_string($byId[$doneCourse->id]['completed_at'])
                    && $byId[$openCourse->id]['completed'] === false
                    && $byId[$openCourse->id]['completed_at'] === null;
            }));
});

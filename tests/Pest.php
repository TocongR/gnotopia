<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * A published course with one module and $lessonCount lessons.
 *
 * @return array{0: Course, 1: Collection<int, Lesson>}
 */
function learningCourse(int $lessonCount = 2, array $attributes = []): array
{
    $course = Course::factory()->published()->create($attributes);
    $module = Module::factory()->for($course)->create(['position' => 1]);

    $lessons = Collection::times(
        $lessonCount,
        fn (int $position) => Lesson::factory()->for($module)->create(['position' => $position]),
    );

    return [$course, $lessons];
}

/**
 * A quiz in the given module with $questionCount questions, each with 3 options
 * (the first option of every question is the correct one).
 */
function quizWithQuestions(Module $module, int $questionCount = 2, array $attributes = []): Quiz
{
    $quiz = Quiz::factory()->for($module)->create($attributes + ['position' => 10]);

    foreach (range(1, $questionCount) as $position) {
        $question = Question::factory()->for($quiz)->create(['position' => $position]);

        Option::factory()->correct()->for($question)->create();
        Option::factory()->count(2)->for($question)->create();
    }

    return $quiz->load('questions.options');
}

/**
 * Answers for a quiz as question id => option id.
 * The first $correctCount questions are answered correctly, the rest wrongly.
 */
function quizAnswers(Quiz $quiz, ?int $correctCount = null): array
{
    $quiz->loadMissing('questions.options');
    $correctCount ??= $quiz->questions->count();

    return $quiz->questions->values()->mapWithKeys(function ($question, $index) use ($correctCount) {
        $option = $index < $correctCount
            ? $question->options->firstWhere('is_correct', true)
            : $question->options->firstWhere('is_correct', false);

        return [$question->id => $option->id];
    })->all();
}

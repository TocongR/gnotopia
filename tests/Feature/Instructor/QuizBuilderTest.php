<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function quizContext(): array
{
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();

    return [$instructor, $course, $module];
}

function quizPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Basics Quiz',
        'pass_percent' => 70,
        'questions' => [[
            'id' => null,
            'body' => 'What is 2 + 2?',
            'options' => [
                ['id' => null, 'body' => '3', 'is_correct' => false],
                ['id' => null, 'body' => '4', 'is_correct' => true],
            ],
        ]],
    ], $overrides);
}

it('creates a quiz with questions and options', function () {
    [$instructor, $course, $module] = quizContext();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload())
        ->assertRedirect(route('instructor.courses.content', $course));

    $quiz = Quiz::firstWhere('title', 'Basics Quiz');

    expect($quiz->module_id)->toEqual($module->id)
        ->and($quiz->pass_percent)->toEqual(70)
        ->and($quiz->questions)->toHaveCount(1)
        ->and($quiz->questions->first()->options)->toHaveCount(2)
        ->and($quiz->questions->first()->options->where('is_correct', true))->toHaveCount(1);
});

it('puts a new quiz after existing lessons and quizzes', function () {
    [$instructor, , $module] = quizContext();
    Lesson::factory()->for($module)->create(['position' => 3]);

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload());

    expect(Quiz::firstWhere('title', 'Basics Quiz')->position)->toEqual(4);
});

it('requires at least one question', function () {
    [$instructor, , $module] = quizContext();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload(['questions' => []]))
        ->assertSessionHasErrors('questions');

    expect(Quiz::count())->toBe(0);
});

it('requires at least two options per question', function () {
    [$instructor, , $module] = quizContext();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload([
            'questions' => [[
                'id' => null,
                'body' => 'Only one?',
                'options' => [['id' => null, 'body' => 'Alone', 'is_correct' => true]],
            ]],
        ]))
        ->assertSessionHasErrors('questions.0.options');
});

it('requires exactly one correct option per question', function (array $flags) {
    [$instructor, , $module] = quizContext();

    $options = collect($flags)
        ->map(fn ($flag, $i) => ['id' => null, 'body' => 'Option '.($i + 1), 'is_correct' => $flag])
        ->all();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload([
            'questions' => [['id' => null, 'body' => 'Pick one?', 'options' => $options]],
        ]))
        ->assertSessionHasErrors('questions.0.options');

    expect(Quiz::count())->toBe(0);
})->with([
    'none correct' => [[false, false]],
    'two correct' => [[true, true]],
    'three options, none correct' => [[false, false, false]],
]);

it('validates the passing score range', function (int $percent) {
    [$instructor, , $module] = quizContext();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.quizzes.store', $module), quizPayload(['pass_percent' => $percent]))
        ->assertSessionHasErrors('pass_percent');
})->with([0, 101]);

it('blocks students and other instructors from the quiz builder', function () {
    [, , $module] = quizContext();
    $quiz = Quiz::factory()->for($module)->create();

    foreach ([User::factory()->instructor()->create(), User::factory()->create()] as $intruder) {
        $this->actingAs($intruder)->get(route('instructor.modules.quizzes.create', $module))->assertForbidden();
        $this->actingAs($intruder)->post(route('instructor.modules.quizzes.store', $module), quizPayload())->assertForbidden();
        $this->actingAs($intruder)->get(route('instructor.quizzes.edit', $quiz))->assertForbidden();
        $this->actingAs($intruder)->put(route('instructor.quizzes.update', $quiz), quizPayload())->assertForbidden();
        $this->actingAs($intruder)->delete(route('instructor.quizzes.destroy', $quiz))->assertForbidden();
    }

    expect(Quiz::count())->toBe(1);
});

it('loads a quiz with its questions and options for editing', function () {
    [$instructor, , $module] = quizContext();
    $quiz = Quiz::factory()->for($module)->create();
    $question = Question::factory()->for($quiz)->create();
    Option::factory()->correct()->for($question)->create();
    Option::factory()->for($question)->create();

    $this->actingAs($instructor)
        ->get(route('instructor.quizzes.edit', $quiz))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Instructor/Quizzes/Edit')
            ->has('quiz.questions', 1)
            ->has('quiz.questions.0.options', 2)
            ->where('quiz.questions.0.options.0.is_correct', true));
});

it('updates in place, keeping the ids of existing questions and options', function () {
    [$instructor, $course, $module] = quizContext();
    $quiz = Quiz::factory()->for($module)->create();

    $q1 = Question::factory()->for($quiz)->create(['position' => 1]);
    $a = Option::factory()->correct()->for($q1)->create(['body' => 'A']);
    $b = Option::factory()->for($q1)->create(['body' => 'B']);

    $q2 = Question::factory()->for($quiz)->create(['position' => 2]);
    Option::factory()->correct()->for($q2)->create();
    Option::factory()->for($q2)->create();

    $this->actingAs($instructor)
        ->put(route('instructor.quizzes.update', $quiz), [
            'title' => 'Renamed',
            'pass_percent' => 80,
            'questions' => [
                [
                    'id' => $q1->id,
                    'body' => 'Edited?',
                    'options' => [
                        ['id' => $a->id, 'body' => 'A edited', 'is_correct' => false],
                        ['id' => $b->id, 'body' => 'B', 'is_correct' => false],
                        ['id' => null, 'body' => 'C', 'is_correct' => true],
                    ],
                ],
                // q2 is left out on purpose, so it must be deleted.
                [
                    'id' => null,
                    'body' => 'Brand new?',
                    'options' => [
                        ['id' => null, 'body' => 'Yes', 'is_correct' => true],
                        ['id' => null, 'body' => 'No', 'is_correct' => false],
                    ],
                ],
            ],
        ])
        ->assertRedirect(route('instructor.courses.content', $course));

    $quiz->refresh();

    expect($quiz->title)->toBe('Renamed')
        ->and($quiz->pass_percent)->toEqual(80)
        ->and($quiz->questions)->toHaveCount(2)
        ->and(Question::whereKey($q2->id)->exists())->toBeFalse()
        ->and($q1->fresh()->body)->toBe('Edited?')
        ->and($q1->options()->count())->toBe(3)
        ->and(Option::find($a->id)->body)->toBe('A edited')
        ->and(Option::find($a->id)->is_correct)->toBeFalse();
});

it('rejects question ids that belong to another quiz', function () {
    [$instructor, , $module] = quizContext();
    $quiz = Quiz::factory()->for($module)->create();
    $foreign = Question::factory()->create();

    $this->actingAs($instructor)
        ->put(route('instructor.quizzes.update', $quiz), quizPayload([
            'questions' => [[
                'id' => $foreign->id,
                'body' => 'Hijacked?',
                'options' => [
                    ['id' => null, 'body' => 'x', 'is_correct' => true],
                    ['id' => null, 'body' => 'y', 'is_correct' => false],
                ],
            ]],
        ]))
        ->assertSessionHasErrors('questions');

    expect($foreign->fresh()->body)->not->toBe('Hijacked?');
});

it('deletes a quiz with its questions and options', function () {
    [$instructor, $course, $module] = quizContext();
    $quiz = Quiz::factory()->for($module)->create();
    $question = Question::factory()->for($quiz)->create();
    Option::factory()->for($question)->create();

    $this->actingAs($instructor)
        ->delete(route('instructor.quizzes.destroy', $quiz))
        ->assertRedirect(route('instructor.courses.content', $course));

    expect(Quiz::count())->toBe(0)
        ->and(Question::count())->toBe(0)
        ->and(Option::count())->toBe(0);
});

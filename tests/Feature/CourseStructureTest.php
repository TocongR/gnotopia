<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;

it('returns lessons in position order', function () {
    $module = Module::factory()->create();

    Lesson::factory()->for($module)->create(['title' => 'Second', 'position' => 2]);
    Lesson::factory()->for($module)->create(['title' => 'First', 'position' => 1]);

    expect($module->lessons->pluck('title')->all())->toBe(['First', 'Second']);
});

it('casts course status to the enum', function () {
    $course = Course::factory()->published()->create();

    expect($course->status)->toBe(CourseStatus::Published)
        ->and($course->isPublished())->toBeTrue();
});

it('only returns published courses from the published scope', function () {
    Course::factory()->published()->count(2)->create();
    Course::factory()->create(); // draft

    expect(Course::published()->count())->toBe(2);
});

it('treats a zero price as free', function () {
    expect(Course::factory()->create()->isFree())->toBeTrue()
        ->and(Course::factory()->create(['price_cents' => 4999])->isFree())->toBeFalse();
});

it('deletes the whole tree when a course is deleted', function () {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->create();
    $quiz = Quiz::factory()->for($module)->create();
    $question = Question::factory()->for($quiz)->create();
    Option::factory()->for($question)->create();

    $course->delete();

    expect(Module::count())->toBe(0)
        ->and(Lesson::count())->toBe(0)
        ->and(Quiz::count())->toBe(0)
        ->and(Question::count())->toBe(0)
        ->and(Option::count())->toBe(0);
});

it('does not allow mass assigning the instructor or status', function () {
    $course = new Course([
        'user_id' => 999,
        'status' => 'published',
        'title' => 'Hacked',
    ]);

    expect($course->user_id)->toBeNull()
        ->and($course->status)->toBeNull()
        ->and($course->title)->toBe('Hacked');
});

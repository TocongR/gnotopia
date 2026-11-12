<?php

use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('marks a lesson complete once, however often it is submitted', function () {
    [$course, $lessons] = learningCourse(1);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();

    $this->actingAs($student)->post(route('student.lessons.complete', $lessons[0]))->assertRedirect();
    $this->actingAs($student)->post(route('student.lessons.complete', $lessons[0]))->assertRedirect();

    expect(LessonProgress::count())->toBe(1);
});

it('can mark a lesson as incomplete again', function () {
    [$course, $lessons] = learningCourse(1);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();
    LessonProgress::factory()->for($student)->for($lessons[0])->create();

    $this->actingAs($student)->delete(route('student.lessons.uncomplete', $lessons[0]))->assertRedirect();

    expect(LessonProgress::count())->toBe(0);
});

it('does not let a student complete lessons of a course they are not in', function () {
    [, $lessons] = learningCourse(1);

    $this->actingAs(User::factory()->create())
        ->post(route('student.lessons.complete', $lessons[0]))
        ->assertForbidden();

    expect(LessonProgress::count())->toBe(0);
});

it('does not let enrollment in one course unlock the lessons of another', function () {
    [$courseA] = learningCourse(1);
    [, $lessonsB] = learningCourse(1);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($courseA)->create();

    $this->actingAs($student)
        ->post(route('student.lessons.complete', $lessonsB[0]))
        ->assertForbidden();

    expect(LessonProgress::count())->toBe(0);
});

it('only affects the students own progress', function () {
    [$course, $lessons] = learningCourse(1);
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    Enrollment::factory()->for($alice)->for($course)->create();
    Enrollment::factory()->for($bob)->for($course)->create();

    $this->actingAs($alice)->post(route('student.lessons.complete', $lessons[0]));

    expect(LessonProgress::where('user_id', $alice->id)->count())->toBe(1)
        ->and(LessonProgress::where('user_id', $bob->id)->count())->toBe(0);
});

it('shows only the students own courses, with progress, on the dashboard', function () {
    [$course, $lessons] = learningCourse(4);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();
    LessonProgress::factory()->for($student)->for($lessons[0])->create();

    // Someone else's enrollment must not appear.
    Enrollment::factory()->create();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Student')
            ->has('courses', 1)
            ->where('courses.0.progress.done', 1)
            ->where('courses.0.progress.total', 4)
            ->where('courses.0.progress.percent', 25));
});

it('shows an empty dashboard for a student with no enrollments', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('student.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->has('courses', 0));
});

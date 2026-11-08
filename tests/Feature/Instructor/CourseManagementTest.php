<?php

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function coursePayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Intro to PHP',
        'description' => 'Learn PHP from scratch.',
        'category_id' => Category::factory()->create()->id,
        'price' => '49.99',
    ], $overrides);
}

function ownedCourse(User $instructor, array $attributes = []): Course
{
    return Course::factory()->for($instructor, 'instructor')->create($attributes);
}

it('lets an instructor create a draft course they own', function () {
    $instructor = User::factory()->instructor()->create();
    $someoneElse = User::factory()->instructor()->create();

    $this->actingAs($instructor)
        ->post(route('instructor.courses.store'), coursePayload([
            // These must be ignored: the server decides them.
            'status' => 'published',
            'user_id' => $someoneElse->id,
        ]))
        ->assertRedirect();

    $course = Course::firstWhere('title', 'Intro to PHP');

    expect($course->user_id)->toEqual($instructor->id)
        ->and($course->status)->toBe(CourseStatus::Draft)
        ->and($course->price_cents)->toBe(4999)
        ->and($course->slug)->toBe('intro-to-php');
});

it('generates a unique slug for duplicate titles', function () {
    $instructor = User::factory()->instructor()->create();

    $this->actingAs($instructor)->post(route('instructor.courses.store'), coursePayload());
    $this->actingAs($instructor)->post(route('instructor.courses.store'), coursePayload());

    expect(Course::pluck('slug')->sort()->values()->all())
        ->toBe(['intro-to-php', 'intro-to-php-2']);
});

it('validates the course form', function () {
    $this->actingAs(User::factory()->instructor()->create())
        ->post(route('instructor.courses.store'), [])
        ->assertSessionHasErrors(['title', 'category_id', 'price']);
});

it('lists only the instructors own courses', function () {
    $instructor = User::factory()->instructor()->create();
    ownedCourse($instructor);
    Course::factory()->create(); // belongs to a different instructor

    $this->actingAs($instructor)
        ->get(route('instructor.courses.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Instructor/Courses/Index')
            ->has('courses.data', 1));
});

it('blocks students from the instructor course pages', function () {
    $student = User::factory()->create();

    $this->actingAs($student)->get(route('instructor.courses.index'))->assertForbidden();
    $this->actingAs($student)->get(route('instructor.courses.create'))->assertForbidden();
});

it('updates an own course without changing its slug', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor, ['title' => 'Old Title', 'slug' => 'old-title']);

    $this->actingAs($instructor)
        ->put(route('instructor.courses.update', $course), coursePayload(['title' => 'New Title']))
        ->assertRedirect();

    $course->refresh();

    expect($course->title)->toBe('New Title')
        ->and($course->slug)->toBe('old-title');
});

it('blocks an instructor from touching another instructors course', function () {
    $intruder = User::factory()->instructor()->create();
    $course = ownedCourse(User::factory()->instructor()->create());

    $this->actingAs($intruder)->get(route('instructor.courses.edit', $course))->assertForbidden();
    $this->actingAs($intruder)->put(route('instructor.courses.update', $course), coursePayload())->assertForbidden();
    $this->actingAs($intruder)->delete(route('instructor.courses.destroy', $course))->assertForbidden();
    $this->actingAs($intruder)->post(route('instructor.courses.publish', $course))->assertForbidden();
});

it('publishes a course that has a module', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor);
    Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('instructor.courses.publish', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Published);
});

it('refuses to publish a course without modules', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor);

    $this->actingAs($instructor)
        ->post(route('instructor.courses.publish', $course))
        ->assertSessionHasErrors('status');

    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

it('refuses to publish a suspended course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor, ['status' => CourseStatus::Suspended]);
    Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('instructor.courses.publish', $course))
        ->assertSessionHasErrors('status');

    expect($course->fresh()->status)->toBe(CourseStatus::Suspended);
});

it('moves a published course back to draft', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor, ['status' => CourseStatus::Published]);

    $this->actingAs($instructor)
        ->delete(route('instructor.courses.unpublish', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

it('deletes an own course', function () {
    $instructor = User::factory()->instructor()->create();
    $course = ownedCourse($instructor);

    $this->actingAs($instructor)
        ->delete(route('instructor.courses.destroy', $course))
        ->assertRedirect(route('instructor.courses.index'));

    expect(Course::count())->toBe(0);
});

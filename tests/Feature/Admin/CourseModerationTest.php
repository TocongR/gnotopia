<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('suspends a course: hidden from the catalog and locked for enrolled students', function () {
    [$course, $lessons] = learningCourse(1);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.courses.suspend', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Suspended);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertForbidden();

    $this->get(route('catalog.show', $course->slug))->assertNotFound();
});

it('stops the instructor from publishing a suspended course', function () {
    [$course] = learningCourse(1);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.courses.suspend', $course));

    $this->actingAs($course->instructor)
        ->post(route('instructor.courses.publish', $course))
        ->assertSessionHasErrors('status');

    expect($course->fresh()->status)->toBe(CourseStatus::Suspended);
});

it('restores a suspended course as a draft', function () {
    [$course] = learningCourse(1, ['status' => CourseStatus::Suspended]);

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.courses.restore', $course))
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

it('leaves a course that is not suspended alone when restore is called', function () {
    [$course] = learningCourse(1);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.courses.restore', $course));

    expect($course->fresh()->status)->toBe(CourseStatus::Published);
});

it('lists courses with the instructor and enrollment count', function () {
    [$course] = learningCourse(1);
    Enrollment::factory()->count(2)->for($course)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.courses.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Courses/Index')
            ->has('courses.data', 1)
            ->where('courses.data.0.enrollments_count', 2)
            ->where('courses.data.0.instructor.name', $course->instructor->name));
});

it('searches and filters the course list', function () {
    $admin = User::factory()->admin()->create();
    Course::factory()->published()->create(['title' => 'Laravel Basics']);
    Course::factory()->create(['title' => 'Cooking 101']);
    Course::factory()->create(['title' => 'Pottery', 'status' => CourseStatus::Suspended]);

    $this->actingAs($admin)
        ->get(route('admin.courses.index', ['q' => 'laravel']))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 1));

    $this->actingAs($admin)
        ->get(route('admin.courses.index', ['status' => 'suspended']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Pottery')
            ->where('filters.status', 'suspended'));
});

it('rejects an unknown status filter', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.courses.index', ['status' => 'archived']))
        ->assertSessionHasErrors('status');
});

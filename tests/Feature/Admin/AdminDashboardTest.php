<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('counts people, courses and learning activity', function () {
    $admin = User::factory()->admin()->create();
    $instructor = User::factory()->instructor()->create();

    $s1 = User::factory()->create();
    $s2 = User::factory()->create();
    User::factory()->suspended()->create(); // a third student, suspended

    $published = Course::factory()->published()->for($instructor, 'instructor')->count(2)->create();
    Course::factory()->for($instructor, 'instructor')->create(); // draft
    Course::factory()->for($instructor, 'instructor')->create(['status' => CourseStatus::Suspended]);

    Enrollment::factory()->for($s1)->for($published[0])->create();
    Enrollment::factory()->for($s2)->for($published[0])->create(['completed_at' => now()]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Admin')
            ->where('users.students', 3)
            ->where('users.instructors', 1)
            ->where('users.admins', 1)
            ->where('users.suspended', 1)
            ->where('courses.published', 2)
            ->where('courses.draft', 1)
            ->where('courses.suspended', 1)
            ->where('enrollments', 2)
            ->where('completions', 1));
});

it('works on an empty platform', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.admins', 1)
            ->where('courses.published', 0)
            ->where('enrollments', 0));
});

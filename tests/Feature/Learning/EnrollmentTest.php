<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('enrolls a student in a free published course', function () {
    [$course] = learningCourse();
    $student = User::factory()->create();

    $this->actingAs($student)
        ->post(route('student.courses.enroll', $course->slug))
        ->assertRedirect(route('student.learn.show', $course->slug));

    $enrollment = Enrollment::first();

    expect($enrollment->user_id)->toEqual($student->id)
        ->and($enrollment->course_id)->toEqual($course->id)
        ->and($enrollment->enrolled_at)->not->toBeNull()
        ->and($enrollment->completed_at)->toBeNull();
});

it('does not create a second enrollment when submitted twice', function () {
    [$course] = learningCourse();
    $student = User::factory()->create();

    $this->actingAs($student)->post(route('student.courses.enroll', $course->slug));
    $this->actingAs($student)
        ->post(route('student.courses.enroll', $course->slug))
        ->assertRedirect(route('student.learn.show', $course->slug));

    expect(Enrollment::count())->toBe(1);
});

it('refuses to enroll in a paid course for now', function () {
    [$course] = learningCourse(2, ['price_cents' => 4999]);

    $this->actingAs(User::factory()->create())
        ->post(route('student.courses.enroll', $course->slug))
        ->assertForbidden();

    expect(Enrollment::count())->toBe(0);
});

it('refuses to enroll in courses that are not published', function (CourseStatus $status) {
    [$course] = learningCourse(2, ['status' => $status]);

    $this->actingAs(User::factory()->create())
        ->post(route('student.courses.enroll', $course->slug))
        ->assertForbidden();

    expect(Enrollment::count())->toBe(0);
})->with([CourseStatus::Draft, CourseStatus::Suspended]);

it('only lets student accounts enroll', function (string $state) {
    [$course] = learningCourse();

    $this->actingAs(User::factory()->{$state}()->create())
        ->post(route('student.courses.enroll', $course->slug))
        ->assertForbidden();

    expect(Enrollment::count())->toBe(0);
})->with(['instructor', 'admin']);

it('sends guests to login when they try to enroll', function () {
    [$course] = learningCourse();

    $this->post(route('student.courses.enroll', $course->slug))
        ->assertRedirect(route('login'));
});

it('tells the course page what the viewer can do', function () {
    [$free] = learningCourse();
    [$paid] = learningCourse(1, ['price_cents' => 5000]);

    // Guest
    $this->get(route('catalog.show', $free->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('viewer.is_student', false)
            ->where('viewer.enrolled', false)
            ->where('viewer.can_enroll', false));

    // Student, not enrolled
    $student = User::factory()->create();

    $this->actingAs($student)->get(route('catalog.show', $free->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('viewer.is_student', true)
            ->where('viewer.can_enroll', true)
            ->where('viewer.enrolled', false));

    $this->actingAs($student)->get(route('catalog.show', $paid->slug))
        ->assertInertia(fn (Assert $page) => $page->where('viewer.can_enroll', false));

    // Student, enrolled
    Enrollment::factory()->for($student)->for($free)->create();

    $this->actingAs($student)->get(route('catalog.show', $free->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('viewer.enrolled', true)
            ->where('viewer.can_enroll', false));

    // Instructor
    $this->actingAs(User::factory()->instructor()->create())
        ->get(route('catalog.show', $free->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('viewer.is_student', false)
            ->where('viewer.can_enroll', false));
});

it('stops an instructor from deleting a course that has enrolled students', function () {
    [$course] = learningCourse();
    Enrollment::factory()->for($course)->create();

    $this->actingAs($course->instructor)
        ->delete(route('instructor.courses.destroy', $course))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Course::count())->toBe(1);
});

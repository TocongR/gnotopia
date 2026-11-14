<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows an instructor numbers for their own courses only', function () {
    $instructor = User::factory()->instructor()->create();
    $published = Course::factory()->published()->for($instructor, 'instructor')->create();
    Course::factory()->for($instructor, 'instructor')->create(); // a draft

    Enrollment::factory()->count(2)->for($published)->create();
    Enrollment::factory()->for($published)->create(['completed_at' => now()]);
    Enrollment::factory()->create(); // belongs to another instructor's course

    $this->actingAs($instructor)
        ->get(route('instructor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Instructor')
            ->where('stats.courses', 2)
            ->where('stats.published', 1)
            ->where('stats.enrollments', 3)
            ->where('stats.completions', 1)
            ->has('courses', 2)
            ->has('recent', 3));
});

it('works for an instructor with no courses', function () {
    $this->actingAs(User::factory()->instructor()->create())
        ->get(route('instructor.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.courses', 0)
            ->has('courses', 0)
            ->has('recent', 0));
});

it('shows student names but never their email addresses', function () {
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $student = User::factory()->create(['name' => 'Maria Santos', 'email' => 'private.student@example.test']);
    Enrollment::factory()->for($student)->for($course)->create();

    $this->actingAs($instructor)
        ->get(route('instructor.dashboard'))
        ->assertDontSee('private.student@example.test')
        ->assertInertia(fn (Assert $page) => $page
            ->where('recent.0.student', 'Maria Santos')
            ->missing('recent.0.email'));

    $this->actingAs($instructor)
        ->get(route('instructor.courses.students', $course))
        ->assertDontSee('private.student@example.test');
});

it('lists the enrolled students of an own course with their progress', function () {
    [$course, $lessons] = learningCourse(4);
    $instructor = $course->instructor;

    $maria = User::factory()->create(['name' => 'Maria Santos']);
    $juan = User::factory()->create(['name' => 'Juan Cruz']);
    Enrollment::factory()->for($maria)->for($course)->create();
    Enrollment::factory()->for($juan)->for($course)->create(['completed_at' => now()]);

    LessonProgress::factory()->for($maria)->for($lessons[0])->create();
    LessonProgress::factory()->for($maria)->for($lessons[1])->create();

    // Progress in a different course must not leak in.
    [, $otherLessons] = learningCourse(1);
    LessonProgress::factory()->for($juan)->for($otherLessons[0])->create();

    $this->actingAs($instructor)
        ->get(route('instructor.courses.students', $course))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Instructor/Courses/Students')
            ->where('course.id', $course->id)
            ->has('students.data', 2)
            ->where('totals.enrolled', 2)
            ->where('totals.completed', 1)
            ->where('students.data', function ($rows) {
                $byName = collect($rows)->keyBy('name');

                return $byName['Maria Santos']['progress'] === ['done' => 2, 'total' => 4, 'percent' => 50]
                    && $byName['Maria Santos']['completed_at'] === null
                    && $byName['Juan Cruz']['progress']['done'] === 0
                    && is_string($byName['Juan Cruz']['completed_at']);
            }));
});

it('counts passed quizzes in the students progress', function () {
    [$course, $lessons] = learningCourse(1);
    $quiz = quizWithQuestions($course->modules()->first(), 2);
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();
    LessonProgress::factory()->for($student)->for($lessons[0])->create();
    QuizAttempt::factory()->for($student)->for($quiz)->create(['passed' => true]);

    $this->actingAs($course->instructor)
        ->get(route('instructor.courses.students', $course))
        ->assertInertia(fn (Assert $page) => $page
            ->where('students.data.0.progress.done', 2)
            ->where('students.data.0.progress.total', 2)
            ->where('students.data.0.progress.percent', 100));
});

it('paginates the student list', function () {
    [$course] = learningCourse(1);
    Enrollment::factory()->count(21)->for($course)->create();

    $this->actingAs($course->instructor)
        ->get(route('instructor.courses.students', $course))
        ->assertInertia(fn (Assert $page) => $page->has('students.data', 20));

    $this->actingAs($course->instructor)
        ->get(route('instructor.courses.students', ['course' => $course, 'page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('students.data', 1));
});

it('does not list students from other courses', function () {
    [$course] = learningCourse(1);
    Enrollment::factory()->for($course)->create();
    Enrollment::factory()->create();

    $this->actingAs($course->instructor)
        ->get(route('instructor.courses.students', $course))
        ->assertInertia(fn (Assert $page) => $page->has('students.data', 1));
});

it('keeps the student list private to the course owner', function () {
    [$course] = learningCourse(1);

    $this->actingAs(User::factory()->instructor()->create())
        ->get(route('instructor.courses.students', $course))
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->get(route('instructor.courses.students', $course))
        ->assertForbidden();

    $this->get(route('instructor.courses.students', $course))
        ->assertRedirect(route('login'));
});

it('shows the enrollment count on the course list', function () {
    [$course] = learningCourse(1);
    Enrollment::factory()->count(3)->for($course)->create();

    $this->actingAs($course->instructor)
        ->get(route('instructor.courses.index'))
        ->assertInertia(fn (Assert $page) => $page->where('courses.data.0.enrollments_count', 3));
});

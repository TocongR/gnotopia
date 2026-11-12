<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function enrolledStudent(Course $course): User
{
    $student = User::factory()->create();
    Enrollment::factory()->for($student)->for($course)->create();

    return $student;
}

it('sends a student to the first lesson of a fresh course', function () {
    [$course, $lessons] = learningCourse(3);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.show', $course->slug))
        ->assertRedirect(route('student.learn.lesson', [$course->slug, $lessons[0]->id]));
});

it('resumes at the first lesson that is not complete', function () {
    [$course, $lessons] = learningCourse(3);
    $student = enrolledStudent($course);
    LessonProgress::factory()->for($student)->for($lessons[0])->create();

    $this->actingAs($student)
        ->get(route('student.learn.show', $course->slug))
        ->assertRedirect(route('student.learn.lesson', [$course->slug, $lessons[1]->id]));
});

it('goes back to the first lesson when everything is complete', function () {
    [$course, $lessons] = learningCourse(2);
    $student = enrolledStudent($course);

    foreach ($lessons as $lesson) {
        LessonProgress::factory()->for($student)->for($lesson)->create();
    }

    $this->actingAs($student)
        ->get(route('student.learn.show', $course->slug))
        ->assertRedirect(route('student.learn.lesson', [$course->slug, $lessons[0]->id]));
});

it('tells the student when a course has no lessons yet', function () {
    [$course] = learningCourse(0);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.show', $course->slug))
        ->assertRedirect(route('student.dashboard'))
        ->assertSessionHas('error');
});

it('blocks students who are not enrolled', function () {
    [$course, $lessons] = learningCourse();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)->get(route('student.learn.show', $course->slug))->assertForbidden();
    $this->actingAs($outsider)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertForbidden();
});

it('blocks instructors and admins from the learning pages', function (string $state) {
    [$course, $lessons] = learningCourse();

    $this->actingAs(User::factory()->{$state}()->create())
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertForbidden();
})->with(['instructor', 'admin']);

it('returns 404 for a lesson that belongs to a different course', function () {
    [$courseA] = learningCourse();
    [, $lessonsB] = learningCourse();
    $student = enrolledStudent($courseA);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$courseA->slug, $lessonsB[0]->id]))
        ->assertNotFound();
});

it('locks a suspended course even for enrolled students', function () {
    [$course, $lessons] = learningCourse(1, ['status' => CourseStatus::Suspended]);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertForbidden();
});

it('keeps access for enrolled students after the course is unpublished', function () {
    [$course, $lessons] = learningCourse(1, ['status' => CourseStatus::Draft]);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertOk();
});

it('gives the player everything it needs', function () {
    [$course, $lessons] = learningCourse(3);
    $student = enrolledStudent($course);
    LessonProgress::factory()->for($student)->for($lessons[0])->create();

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[1]->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Learn/Lesson')
            ->where('lesson.id', $lessons[1]->id)
            ->where('prev.id', $lessons[0]->id)
            ->where('next.id', $lessons[2]->id)
            ->where('progress.done', 1)
            ->where('progress.total', 3)
            ->where('modules.0.items.0.completed', true)
            ->where('modules.0.items.1.completed', false));
});

it('has no previous link on the first lesson and no next link on the last', function () {
    [$course, $lessons] = learningCourse(2);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[0]->id]))
        ->assertInertia(fn (Assert $page) => $page->where('prev', null)->where('next.id', $lessons[1]->id));

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $lessons[1]->id]))
        ->assertInertia(fn (Assert $page) => $page->where('prev.id', $lessons[0]->id)->where('next', null));
});

it('sanitizes article bodies and converts video links when rendering', function () {
    $course = Course::factory()->published()->create();
    $module = Module::factory()->for($course)->create(['position' => 1]);
    $article = Lesson::factory()->for($module)->create([
        'position' => 1,
        'body' => '<p>Hello</p><script>alert(1)</script>',
    ]);
    $video = Lesson::factory()->video()->for($module)->create([
        'position' => 2,
        'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
    ]);
    $student = enrolledStudent($course);

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $article->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('lesson.kind', 'article')
            ->where('lesson.embed_url', null)
            ->where('lesson.body', fn ($body) => str_contains($body, 'Hello') && ! str_contains($body, '<script')));

    $this->actingAs($student)
        ->get(route('student.learn.lesson', [$course->slug, $video->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('lesson.kind', 'video')
            ->where('lesson.body', null)
            ->where('lesson.embed_url', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'));
});

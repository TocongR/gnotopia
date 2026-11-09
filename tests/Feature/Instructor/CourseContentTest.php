<?php

use App\Enums\CourseStatus;
use App\Enums\LessonType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function instructorWithCourse(array $attributes = []): array
{
    $instructor = User::factory()->instructor()->create();
    $course = Course::factory()->for($instructor, 'instructor')->create($attributes);

    return [$instructor, $course];
}

it('shows lessons and quizzes merged in position order', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create(['position' => 1]);
    Lesson::factory()->for($module)->create(['position' => 1]);
    Quiz::factory()->for($module)->create(['position' => 2]);
    Lesson::factory()->for($module)->create(['position' => 3]);

    $this->actingAs($instructor)
        ->get(route('instructor.courses.content', $course))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Instructor/Courses/Content')
            ->has('modules', 1)
            ->has('modules.0.items', 3)
            ->where('modules.0.items.1.type', 'quiz'));
});

it('blocks other instructors from the content page and module actions', function () {
    [, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $intruder = User::factory()->instructor()->create();

    $this->actingAs($intruder)->get(route('instructor.courses.content', $course))->assertForbidden();
    $this->actingAs($intruder)->post(route('instructor.courses.modules.store', $course), ['title' => 'X'])->assertForbidden();
    $this->actingAs($intruder)->put(route('instructor.modules.update', $module), ['title' => 'X'])->assertForbidden();
    $this->actingAs($intruder)->delete(route('instructor.modules.destroy', $module))->assertForbidden();
});

it('adds modules at the end of the course', function () {
    [$instructor, $course] = instructorWithCourse();

    $this->actingAs($instructor)
        ->post(route('instructor.courses.modules.store', $course), ['title' => 'Basics'])
        ->assertRedirect();
    $this->actingAs($instructor)
        ->post(route('instructor.courses.modules.store', $course), ['title' => 'Advanced']);

    expect($course->modules()->pluck('title')->all())->toBe(['Basics', 'Advanced']);
});

it('requires a module title', function () {
    [$instructor, $course] = instructorWithCourse();

    $this->actingAs($instructor)
        ->post(route('instructor.courses.modules.store', $course), ['title' => ''])
        ->assertSessionHasErrors('title');
});

it('renames a module', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create(['title' => 'Old']);

    $this->actingAs($instructor)
        ->put(route('instructor.modules.update', $module), ['title' => 'New'])
        ->assertRedirect();

    expect($module->fresh()->title)->toBe('New');
});

it('deletes a module with its lessons', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->create();

    $this->actingAs($instructor)->delete(route('instructor.modules.destroy', $module))->assertRedirect();

    expect(Module::count())->toBe(0)->and(Lesson::count())->toBe(0);
});

it('does not delete the last module of a published course', function () {
    [$instructor, $course] = instructorWithCourse(['status' => CourseStatus::Published]);
    $module = Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->delete(route('instructor.modules.destroy', $module))
        ->assertSessionHasErrors('module');

    expect(Module::count())->toBe(1);
});

it('reorders modules', function () {
    [$instructor, $course] = instructorWithCourse();
    $first = Module::factory()->for($course)->create(['position' => 1]);
    $second = Module::factory()->for($course)->create(['position' => 2]);

    $this->actingAs($instructor)
        ->put(route('instructor.courses.modules.reorder', $course), ['ids' => [$second->id, $first->id]])
        ->assertRedirect();

    expect($second->fresh()->position)->toEqual(1)
        ->and($first->fresh()->position)->toEqual(2);
});

it('rejects a stale module order', function () {
    [$instructor, $course] = instructorWithCourse();
    $first = Module::factory()->for($course)->create(['position' => 1]);
    Module::factory()->for($course)->create(['position' => 2]);

    $this->actingAs($instructor)
        ->put(route('instructor.courses.modules.reorder', $course), ['ids' => [$first->id]])
        ->assertSessionHasErrors('ids');
});

it('creates an article lesson and strips dangerous html', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Hello',
            'type' => 'article',
            'body' => '<p onclick="alert(1)">Hi <a href="javascript:alert(1)">link</a></p><script>alert(2)</script>',
        ])
        ->assertRedirect(route('instructor.courses.content', $course));

    $body = Lesson::firstWhere('title', 'Hello')->body;

    expect($body)->toContain('Hi');
    expect($body)->not->toContain('<script');
    expect($body)->not->toContain('onclick');
    expect($body)->not->toContain('javascript:');
});

it('rejects an empty article', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Empty',
            'type' => 'article',
            'body' => '<p></p>',
        ])
        ->assertSessionHasErrors('body');

    expect(Lesson::count())->toBe(0);
});

it('accepts only youtube or vimeo links for video lessons', function (string $url, bool $valid) {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();

    $response = $this->actingAs($instructor)
        ->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Watch',
            'type' => 'video',
            'video_url' => $url,
        ]);

    if ($valid) {
        $response->assertSessionHasNoErrors();
        expect(Lesson::count())->toBe(1);
    } else {
        $response->assertSessionHasErrors('video_url');
        expect(Lesson::count())->toBe(0);
    }
})->with([
    'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', true],
    'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', true],
    'vimeo' => ['https://vimeo.com/76979871', true],
    'random mp4' => ['https://example.com/video.mp4', false],
    'javascript url' => ['javascript:alert(1)', false],
]);

it('puts a new lesson after existing lessons and quizzes', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->create(['position' => 1]);
    Quiz::factory()->for($module)->create(['position' => 4]);

    $this->actingAs($instructor)
        ->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Latest',
            'type' => 'article',
            'body' => '<p>Content</p>',
        ]);

    expect(Lesson::firstWhere('title', 'Latest')->position)->toEqual(5);
});

it('switches a lesson from article to video and clears the body', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $this->actingAs($instructor)
        ->put(route('instructor.lessons.update', $lesson), [
            'title' => 'Now a video',
            'type' => 'video',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])
        ->assertRedirect(route('instructor.courses.content', $course));

    $lesson->refresh();

    expect($lesson->type)->toBe(LessonType::Video)
        ->and($lesson->body)->toBeNull()
        ->and($lesson->video_url)->toBe('https://youtu.be/dQw4w9WgXcQ');
});

it('blocks other instructors from editing or deleting a lesson', function () {
    [, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $intruder = User::factory()->instructor()->create();

    $this->actingAs($intruder)->get(route('instructor.lessons.edit', $lesson))->assertForbidden();
    $this->actingAs($intruder)->put(route('instructor.lessons.update', $lesson), [
        'title' => 'X', 'type' => 'article', 'body' => '<p>x</p>',
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('instructor.lessons.destroy', $lesson))->assertForbidden();
});

it('deletes a lesson', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();

    $this->actingAs($instructor)
        ->delete(route('instructor.lessons.destroy', $lesson))
        ->assertRedirect(route('instructor.courses.content', $course));

    expect(Lesson::count())->toBe(0);
});

it('reorders lessons and quizzes together', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create(['position' => 1]);
    $quiz = Quiz::factory()->for($module)->create(['position' => 2]);

    $this->actingAs($instructor)
        ->put(route('instructor.modules.items.reorder', $module), [
            'items' => [
                ['type' => 'quiz', 'id' => $quiz->id],
                ['type' => 'lesson', 'id' => $lesson->id],
            ],
        ])
        ->assertRedirect();

    expect($quiz->fresh()->position)->toEqual(1)
        ->and($lesson->fresh()->position)->toEqual(2);
});

it('rejects an item order that is missing something', function () {
    [$instructor, $course] = instructorWithCourse();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create(['position' => 1]);
    Quiz::factory()->for($module)->create(['position' => 2]);

    $this->actingAs($instructor)
        ->put(route('instructor.modules.items.reorder', $module), [
            'items' => [['type' => 'lesson', 'id' => $lesson->id]],
        ])
        ->assertSessionHasErrors('items');
});

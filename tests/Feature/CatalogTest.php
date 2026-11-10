<?php

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('lets guests open the home page and the catalog', function () {
    $this->get(route('home'))->assertOk();
    $this->get(route('catalog.index'))->assertOk();
});

it('lets logged in users open the catalog too', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('catalog.index'))
        ->assertOk();
});

it('lists only published courses', function () {
    Course::factory()->published()->create(['title' => 'Visible']);
    Course::factory()->create(['title' => 'A Draft']);
    Course::factory()->create(['title' => 'Suspended One', 'status' => CourseStatus::Suspended]);

    $this->get(route('catalog.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Index')
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Visible'));
});

it('never exposes instructor emails in the catalog', function () {
    Course::factory()->published()->create();

    $this->get(route('catalog.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->missing('courses.data.0.email')
            ->missing('courses.data.0.instructor.email'));
});

it('searches titles and descriptions', function () {
    Course::factory()->published()->create(['title' => 'Laravel Basics', 'description' => 'PHP framework']);
    Course::factory()->published()->create(['title' => 'Cooking 101', 'description' => 'Quantum soup recipes']);
    Course::factory()->published()->create(['title' => 'Pottery', 'description' => 'Clay work']);

    $this->get(route('catalog.index', ['q' => 'laravel']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Laravel Basics')
            ->where('filters.q', 'laravel'));

    $this->get(route('catalog.index', ['q' => 'quantum']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Cooking 101'));
});

it('treats percent and underscore in a search as plain characters', function () {
    Course::factory()->published()->create(['title' => '100% Pure PHP', 'description' => 'x']);
    Course::factory()->published()->create(['title' => 'Other Course', 'description' => 'y']);

    $this->get(route('catalog.index', ['q' => '100%']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', '100% Pure PHP'));

    // A bare wildcard must not match everything.
    $this->get(route('catalog.index', ['q' => '%']))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 1));

    $this->get(route('catalog.index', ['q' => '_']))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 0));
});

it('does not search unpublished courses', function () {
    Course::factory()->create(['title' => 'Secret Draft']);

    $this->get(route('catalog.index', ['q' => 'secret']))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 0));
});

it('filters by category', function () {
    $design = Category::factory()->create(['name' => 'Design', 'slug' => 'design']);
    Course::factory()->published()->for($design)->create(['title' => 'Logo Design']);
    Course::factory()->published()->create(['title' => 'Something Else']);

    $this->get(route('catalog.index', ['category' => 'design']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Logo Design')
            ->where('filters.category', 'design'));
});

it('only offers categories that have published courses', function () {
    Course::factory()->published()->for(Category::factory()->create(['name' => 'Used']))->create();
    Course::factory()->for(Category::factory()->create(['name' => 'Draft Only']))->create();
    Category::factory()->create(['name' => 'Empty']);

    $this->get(route('catalog.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('categories', 1)
            ->where('categories.0.name', 'Used'));
});

it('paginates twelve courses per page', function () {
    Course::factory()->published()->count(13)->create();

    $this->get(route('catalog.index'))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 12));

    $this->get(route('catalog.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('courses.data', 1));
});

it('rejects an overly long search term', function () {
    $this->get(route('catalog.index', ['q' => str_repeat('a', 101)]))
        ->assertSessionHasErrors('q');
});

it('shows at most six courses on the home page', function () {
    Course::factory()->published()->count(8)->create();
    Course::factory()->create(); // draft

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('courses', 6));
});

it('shows a published course with its outline', function () {
    $instructor = User::factory()->instructor()->create(['name' => 'Ada Lovelace']);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create(['title' => 'Getting Started', 'position' => 1]);
    Lesson::factory()->for($module)->create(['title' => 'Intro', 'position' => 1]);
    Quiz::factory()->for($module)->create(['title' => 'Check-in', 'position' => 2]);

    $this->get(route('catalog.show', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->where('course.title', $course->title)
            ->where('course.instructor.name', 'Ada Lovelace')
            ->where('course.lessons_count', 1)
            ->where('course.quizzes_count', 1)
            ->has('course.modules', 1)
            ->has('course.modules.0.items', 2)
            ->where('course.modules.0.items.0.kind', 'article')
            ->where('course.modules.0.items.1.kind', 'quiz'));
});

it('does not leak lesson content, video links or emails on the public page', function () {
    $instructor = User::factory()->instructor()->create(['email' => 'teacher@private.test']);
    $course = Course::factory()->published()->for($instructor, 'instructor')->create();
    $module = Module::factory()->for($course)->create();
    Lesson::factory()->for($module)->create(['body' => '<p>SECRET LESSON BODY</p>', 'position' => 1]);
    Lesson::factory()->video()->for($module)->create(['video_url' => 'https://youtu.be/SECRETVIDEO1', 'position' => 2]);

    $this->get(route('catalog.show', $course->slug))
        ->assertOk()
        ->assertDontSee('SECRET LESSON BODY')
        ->assertDontSee('SECRETVIDEO1')
        ->assertDontSee('teacher@private.test')
        ->assertInertia(fn (Assert $page) => $page
            ->missing('course.modules.0.items.0.body')
            ->missing('course.modules.0.items.1.video_url')
            ->missing('course.instructor.email'));
});

it('returns 404 for courses that are not published', function (CourseStatus $status) {
    $owner = User::factory()->instructor()->create();
    $course = Course::factory()->for($owner, 'instructor')->create(['status' => $status]);

    $this->get(route('catalog.show', $course->slug))->assertNotFound();

    // Not even the owner sees the public page of an unpublished course.
    $this->actingAs($owner)->get(route('catalog.show', $course->slug))->assertNotFound();
})->with([CourseStatus::Draft, CourseStatus::Suspended]);

it('returns 404 for an unknown slug', function () {
    $this->get(route('catalog.show', 'does-not-exist'))->assertNotFound();
});

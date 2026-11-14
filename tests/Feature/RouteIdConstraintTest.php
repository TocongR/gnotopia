<?php

use App\Models\User;

function userForRole(string $role): User
{
    return match ($role) {
        'admin' => User::factory()->admin()->create(),
        'instructor' => User::factory()->instructor()->create(),
        default => User::factory()->create(),
    };
}

it('returns 404 instead of a database error for malformed ids', function (string $role, string $method, string $url) {
    $this->actingAs(userForRole($role))->call($method, $url)->assertNotFound();
})->with([
    'instructor course, letters' => ['instructor', 'GET', '/instructor/courses/abc/edit'],
    'instructor course, too big' => ['instructor', 'GET', '/instructor/courses/99999999999999999999/edit'],
    'instructor content page' => ['instructor', 'GET', '/instructor/courses/abc/content'],
    'instructor lesson' => ['instructor', 'GET', '/instructor/lessons/abc/edit'],
    'instructor quiz' => ['instructor', 'GET', '/instructor/quizzes/abc/edit'],
    'instructor module delete' => ['instructor', 'DELETE', '/instructor/modules/abc'],
    'admin course suspend' => ['admin', 'POST', '/admin/courses/abc/suspension'],
    'admin user role' => ['admin', 'PUT', '/admin/users/abc/role'],
    'student lesson' => ['student', 'GET', '/student/learn/some-slug/lessons/abc'],
    'student quiz' => ['student', 'GET', '/student/learn/some-slug/quizzes/abc'],
    'student attempt' => ['student', 'GET', '/student/learn/some-slug/quizzes/1/attempts/abc'],
    'student complete lesson' => ['student', 'POST', '/student/lessons/abc/complete'],
]);

it('still resolves slugs on the public and student routes', function () {
    [$course] = learningCourse(1);

    $this->get(route('catalog.show', $course->slug))->assertOk();
});

it('returns 404 for an unknown course slug without a database error', function () {
    $this->get('/courses/abc')->assertNotFound();
});

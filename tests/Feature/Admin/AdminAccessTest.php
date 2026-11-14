<?php

use App\Models\Course;
use App\Models\User;

function makeUser(string $role): User
{
    return match ($role) {
        'instructor' => User::factory()->instructor()->create(),
        'admin' => User::factory()->admin()->create(),
        default => User::factory()->create(),
    };
}

/** @return array<int, array{0: string, 1: string, 2: array}> */
function adminEndpoints(): array
{
    $target = User::factory()->create();
    $course = Course::factory()->create();

    return [
        ['GET', route('admin.dashboard'), []],
        ['GET', route('admin.users.index'), []],
        ['GET', route('admin.courses.index'), []],
        ['PUT', route('admin.users.role', $target), ['role' => 'admin']],
        ['POST', route('admin.users.suspend', $target), []],
        ['DELETE', route('admin.users.unsuspend', $target), []],
        ['POST', route('admin.courses.suspend', $course), []],
        ['DELETE', route('admin.courses.restore', $course), []],
    ];
}

it('keeps every admin route away from students and instructors', function (string $role) {
    $user = makeUser($role);

    foreach (adminEndpoints() as [$method, $url, $data]) {
        $this->actingAs($user)->call($method, $url, $data)->assertForbidden();
    }
})->with(['student', 'instructor']);

it('sends guests to the login page', function () {
    foreach (adminEndpoints() as [$method, $url, $data]) {
        $this->call($method, $url, $data)->assertRedirect(route('login'));
    }
});

it('blocks a suspended admin like any other suspended user', function () {
    $this->actingAs(User::factory()->admin()->suspended()->create())
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('lets an admin open the three admin pages', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.courses.index'))->assertOk();
});

<?php

use App\Enums\Role;
use App\Models\User;

it('sends each role to its own dashboard', function (Role $role, string $routeName) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($routeName));
})->with([
    'student' => [Role::Student, 'student.dashboard'],
    'instructor' => [Role::Instructor, 'instructor.dashboard'],
    'admin' => [Role::Admin, 'admin.dashboard'],
]);

it('lets each role open its own dashboard', function (Role $role, string $routeName) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get(route($routeName))->assertOk();
})->with([
    'student' => [Role::Student, 'student.dashboard'],
    'instructor' => [Role::Instructor, 'instructor.dashboard'],
    'admin' => [Role::Admin, 'admin.dashboard'],
]);

it('blocks users from other roles dashboards', function (Role $role, string $routeName) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get(route($routeName))->assertForbidden();
})->with([
    'student -> instructor' => [Role::Student, 'instructor.dashboard'],
    'student -> admin' => [Role::Student, 'admin.dashboard'],
    'instructor -> student' => [Role::Instructor, 'student.dashboard'],
    'instructor -> admin' => [Role::Instructor, 'admin.dashboard'],
    'admin -> student' => [Role::Admin, 'student.dashboard'],
]);

it('redirects guests to login', function () {
    $this->get(route('student.dashboard'))->assertRedirect(route('login'));
});

it('logs out suspended users on their next request', function () {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

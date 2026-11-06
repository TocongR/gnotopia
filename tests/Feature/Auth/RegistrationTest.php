<?php

use App\Enums\Role;
use App\Models\User;

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('new users can register as student or instructor', function (string $role) {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => $role,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(User::where('email', 'test@example.com')->first()->role)
        ->toBe(Role::from($role));
})->with(['student', 'instructor']);

test('users cannot register as admin', function () {
    $response = $this->post('/register', [
        'name' => 'Sneaky User',
        'email' => 'sneaky@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
    expect(User::where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

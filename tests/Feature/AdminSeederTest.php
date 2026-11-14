<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

it('creates the admin from config', function () {
    config(['gnotopia.admin.email' => 'boss@example.test', 'gnotopia.admin.password' => 's3cret-pass']);

    $this->seed(AdminSeeder::class);

    $admin = User::firstWhere('email', 'boss@example.test');

    expect($admin->role)->toBe(Role::Admin)
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('s3cret-pass', $admin->password))->toBeTrue();
});

it('never resets an existing admin', function () {
    $admin = User::factory()->admin()->create(['email' => 'boss@example.test']);
    config(['gnotopia.admin.email' => 'boss@example.test', 'gnotopia.admin.password' => 'new-password']);

    $this->seed(AdminSeeder::class);

    expect(Hash::check('password', $admin->fresh()->password))->toBeTrue()
        ->and(User::count())->toBe(1);
});

it('refuses to seed an admin in production without a password', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['gnotopia.admin.password' => null]);

    // Run the seeder directly: `db:seed` itself asks for confirmation in production.
    expect(fn () => (new AdminSeeder)->run())->toThrow(RuntimeException::class);
    expect(User::count())->toBe(0);
});

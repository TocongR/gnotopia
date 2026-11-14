<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('gnotopia.admin.email');
        $password = config('gnotopia.admin.password');

        if (! $password) {
            if (app()->isProduction()) {
                throw new \RuntimeException('Set ADMIN_PASSWORD in .env before seeding the admin account.');
            }

            $password = 'password'; // local development only
        }

        // Never touch an existing account: running the seeder again must not reset a real admin's password.
        if (User::where('email', $email)->exists()) {
            return;
        }

        $admin = new User(['name' => 'Admin', 'email' => $email]);
        $admin->password = Hash::make($password);
        $admin->role = Role::Admin;
        $admin->email_verified_at = now();
        $admin->save();
    }
}

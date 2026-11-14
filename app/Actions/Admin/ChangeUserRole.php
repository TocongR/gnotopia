<?php

namespace App\Actions\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ChangeUserRole
{
    public function handle(User $user, Role $role): void
    {
        if ($user->role === $role) {
            return;
        }

        // Changing these roles would strand the user's data: courses nobody can manage,
        // or enrollments the student can no longer learn from.
        if ($user->role === Role::Instructor && $user->courses()->exists()) {
            throw ValidationException::withMessages([
                'role' => "{$user->name} owns courses, so their role cannot be changed. Suspend the account instead.",
            ]);
        }

        if ($user->role === Role::Student && $user->enrollments()->exists()) {
            throw ValidationException::withMessages([
                'role' => "{$user->name} is enrolled in courses, so their role cannot be changed. Suspend the account instead.",
            ]);
        }

        $user->role = $role;
        $user->save();
    }
}

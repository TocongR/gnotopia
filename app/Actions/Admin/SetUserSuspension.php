<?php

namespace App\Actions\Admin;

use App\Models\User;

class SetUserSuspension
{
    public function handle(User $user, bool $suspended): void
    {
        // Suspending twice keeps the original timestamp.
        $user->suspended_at = $suspended ? ($user->suspended_at ?? now()) : null;
        $user->save();
    }
}

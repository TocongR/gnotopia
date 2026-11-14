<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    /** Admins manage other people's accounts, never their own. */
    public function manage(User $actor, User $target): bool
    {
        return $actor->hasRole(Role::Admin) && $actor->isNot($target);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SetUserSuspension;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UserSuspensionController extends Controller
{
    public function store(User $user, SetUserSuspension $setSuspension): RedirectResponse
    {
        Gate::authorize('manage', $user);

        $setSuspension->handle($user, true);

        return back()->with('success', "{$user->name} has been suspended.");
    }

    public function destroy(User $user, SetUserSuspension $setSuspension): RedirectResponse
    {
        Gate::authorize('manage', $user);

        $setSuspension->handle($user, false);

        return back()->with('success', "{$user->name} can log in again.");
    }
}

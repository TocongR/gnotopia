<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeUserRole;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserRoleController extends Controller
{
    public function update(ChangeUserRoleRequest $request, User $user, ChangeUserRole $changeUserRole): RedirectResponse
    {
        $role = Role::from($request->validated('role'));

        $changeUserRole->handle($user, $role);

        return back()->with('success', "{$user->name} is now {$role->label()}.");
    }
}

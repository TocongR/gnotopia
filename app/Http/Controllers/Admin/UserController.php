<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(UserIndexRequest $request): Response
    {
        $term = $request->validated('q');
        $role = $request->validated('role');

        $users = User::query()
            ->search($term)
            ->when($role, fn ($query) => $query->where('role', $role))
            ->withCount(['courses', 'enrollments'])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'suspended' => $user->isSuspended(),
                'is_self' => $user->is($request->user()),
                'courses_count' => $user->courses_count,
                'enrollments_count' => $user->enrollments_count,
                'joined' => $user->created_at->toFormattedDateString(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => collect(Role::cases())->map(fn (Role $role) => $role->value)->all(),
            'filters' => ['q' => $term ?? '', 'role' => $role ?? ''],
        ]);
    }
}

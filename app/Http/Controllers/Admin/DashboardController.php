<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // The query builder is used on purpose: Eloquent would turn the role and status
        // columns into enums, and enums cannot be used as array keys.
        $roles = DB::table('users')->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');
        $statuses = DB::table('courses')->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('Dashboard/Admin', [
            'users' => [
                'students' => (int) ($roles[Role::Student->value] ?? 0),
                'instructors' => (int) ($roles[Role::Instructor->value] ?? 0),
                'admins' => (int) ($roles[Role::Admin->value] ?? 0),
                'suspended' => User::whereNotNull('suspended_at')->count(),
            ],
            'courses' => [
                'published' => (int) ($statuses[CourseStatus::Published->value] ?? 0),
                'draft' => (int) ($statuses[CourseStatus::Draft->value] ?? 0),
                'suspended' => (int) ($statuses[CourseStatus::Suspended->value] ?? 0),
            ],
            'enrollments' => Enrollment::count(),
            'completions' => Enrollment::whereNotNull('completed_at')->count(),
        ]);
    }
}

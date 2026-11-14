<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $courses = $request->user()->courses()
            ->withCount([
                'enrollments',
                'enrollments as completions_count' => fn ($query) => $query->whereNotNull('completed_at'),
            ])
            ->latest('updated_at')
            ->orderByDesc('id')
            ->get();

        $recent = Enrollment::query()
            ->whereIn('course_id', $courses->pluck('id'))
            ->with(['user:id,name', 'course:id,title'])
            ->latest('enrolled_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard/Instructor', [
            'stats' => [
                'courses' => $courses->count(),
                'published' => $courses->filter(fn (Course $course) => $course->isPublished())->count(),
                'enrollments' => (int) $courses->sum('enrollments_count'),
                'completions' => (int) $courses->sum('completions_count'),
            ],
            'courses' => $courses->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status->value,
                'enrollments' => $course->enrollments_count,
                'completions' => $course->completions_count,
            ])->values(),
            // Names only: instructors do not get students' email addresses.
            'recent' => $recent->map(fn (Enrollment $enrollment) => [
                'id' => $enrollment->id,
                'student' => $enrollment->user->name,
                'course' => $enrollment->course->title,
                'completed' => $enrollment->completed_at !== null,
                'enrolled_at' => $enrollment->enrolled_at->diffForHumans(),
            ])->values(),
        ]);
    }
}

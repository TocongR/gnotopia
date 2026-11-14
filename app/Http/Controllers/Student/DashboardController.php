<?php

namespace App\Http\Controllers\Student;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ProgressService $progress): Response
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course.category:id,name', 'course.instructor:id,name'])
            ->latest('enrolled_at')
            ->orderByDesc('id')
            ->get();

        $summaries = $progress->summaries($user, $enrollments->pluck('course_id'));

        return Inertia::render('Dashboard/Student', [
            'courses' => $enrollments->map(fn (Enrollment $enrollment) => [
                'id' => $enrollment->course->id,
                'title' => $enrollment->course->title,
                'slug' => $enrollment->course->slug,
                'category' => $enrollment->course->category->name,
                'instructor' => $enrollment->course->instructor->name,
                'suspended' => $enrollment->course->status === CourseStatus::Suspended,
                'completed' => $enrollment->completed_at !== null,
                'completed_at' => $enrollment->completed_at?->toFormattedDateString(),
                'progress' => $summaries[$enrollment->course_id],
            ])->values(),
        ]);
    }
}

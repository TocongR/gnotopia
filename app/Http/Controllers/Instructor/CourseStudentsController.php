<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseStudentsController extends Controller
{
    public function __invoke(Course $course, ProgressService $progress): Response
    {
        Gate::authorize('viewStudents', $course);

        $enrollments = $course->enrollments()
            ->with('user:id,name')
            ->latest('enrolled_at')
            ->orderByDesc('id')
            ->paginate(20);

        $summaries = $progress->forCourse($course, $enrollments->getCollection()->pluck('user_id'));

        $students = $enrollments->through(fn (Enrollment $enrollment) => [
            'id' => $enrollment->id,
            'name' => $enrollment->user->name,
            'enrolled_at' => $enrollment->enrolled_at->toFormattedDateString(),
            'completed_at' => $enrollment->completed_at?->toFormattedDateString(),
            'progress' => $summaries[$enrollment->user_id],
        ]);

        return Inertia::render('Instructor/Courses/Students', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status->value,
            ],
            'students' => $students,
            'totals' => [
                'enrolled' => $course->enrollments()->count(),
                'completed' => $course->enrollments()->whereNotNull('completed_at')->count(),
            ],
        ]);
    }
}

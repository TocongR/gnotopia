<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseIndexRequest;
use App\Models\Course;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(CourseIndexRequest $request): Response
    {
        $term = $request->validated('q');
        $status = $request->validated('status');

        $courses = Course::query()
            ->search($term)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['instructor:id,name,email', 'category:id,name'])
            ->withCount('enrollments')
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => $course->status->value,
                'category' => $course->category->name,
                'instructor' => $course->instructor->only('name', 'email'),
                'enrollments_count' => $course->enrollments_count,
            ]);

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'statuses' => collect(CourseStatus::cases())->map(fn (CourseStatus $status) => $status->value)->all(),
            'filters' => ['q' => $term ?? '', 'status' => $status ?? ''],
        ]);
    }
}

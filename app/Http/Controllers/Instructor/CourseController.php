<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Courses\CreateCourse;
use App\Actions\Courses\UpdateCourse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\SaveCourseRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = $request->user()->courses()
            ->with('category:id,name')
            ->withCount(['modules', 'enrollments'])
            ->latest()
            ->paginate(10)
            ->through(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status->value,
                'price_cents' => $course->price_cents,
                'category' => $course->category->name,
                'modules_count' => $course->modules_count,
                'enrollments_count' => $course->enrollments_count,
                'updated_at' => $course->updated_at->diffForHumans(),
            ]);

        return Inertia::render('Instructor/Courses/Index', ['courses' => $courses]);
    }

    public function create(): Response
    {
        return Inertia::render('Instructor/Courses/Create', [
            'categories' => $this->categories(),
        ]);
    }

    public function store(SaveCourseRequest $request, CreateCourse $createCourse): RedirectResponse
    {
        $course = $createCourse->handle($request->user(), $request->courseData());

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('success', 'Course created. It stays a draft until you publish it.');
    }

    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        return Inertia::render('Instructor/Courses/Edit', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'category_id' => $course->category_id,
                'price' => number_format($course->price_cents / 100, 2, '.', ''),
                'status' => $course->status->value,
                'modules_count' => $course->modules()->count(),
            ],
            'categories' => $this->categories(),
        ]);
    }

    public function update(SaveCourseRequest $request, Course $course, UpdateCourse $updateCourse): RedirectResponse
    {
        $updateCourse->handle($course, $request->courseData());

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('delete', $course);

        if ($course->enrollments()->exists()) {
            return back()->with('error', 'This course has enrolled students, so it cannot be deleted. Unpublish it instead.');
        }

        $course->delete();

        return redirect()
            ->route('instructor.courses.index')
            ->with('success', 'Course deleted.');
    }

    private function categories()
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }
}

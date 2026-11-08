<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Courses\PublishCourse;
use App\Actions\Courses\UnpublishCourse;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CoursePublishController extends Controller
{
    public function store(Course $course, PublishCourse $publishCourse): RedirectResponse
    {
        Gate::authorize('publish', $course);

        $publishCourse->handle($course);

        return back()->with('success', 'Course published.');
    }

    public function destroy(Course $course, UnpublishCourse $unpublishCourse): RedirectResponse
    {
        Gate::authorize('publish', $course);

        $unpublishCourse->handle($course);

        return back()->with('success', 'Course moved back to draft.');
    }
}

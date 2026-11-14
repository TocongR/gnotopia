<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SetCourseSuspension;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CourseSuspensionController extends Controller
{
    public function store(Course $course, SetCourseSuspension $setSuspension): RedirectResponse
    {
        Gate::authorize('moderate', $course);

        $setSuspension->handle($course, true);

        return back()->with('success', 'Course suspended.');
    }

    public function destroy(Course $course, SetCourseSuspension $setSuspension): RedirectResponse
    {
        Gate::authorize('moderate', $course);

        $setSuspension->handle($course, false);

        return back()->with('success', 'Course restored as a draft. The instructor can publish it again.');
    }
}

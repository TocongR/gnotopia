<?php

namespace App\Http\Controllers\Student;

use App\Actions\Learning\EnrollStudent;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course, EnrollStudent $enrollStudent): RedirectResponse
    {
        $student = $request->user();

        // Already enrolled (or a double click): just carry on learning.
        if ($student->isEnrolledIn($course)) {
            return redirect()->route('student.learn.show', $course->slug);
        }

        Gate::authorize('enroll', $course);

        $enrollStudent->handle($student, $course);

        return redirect()
            ->route('student.learn.show', $course->slug)
            ->with('success', 'You are enrolled. Happy learning!');
    }
}

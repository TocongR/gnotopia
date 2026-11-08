<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Validation\ValidationException;

class PublishCourse
{
    public function handle(Course $course): void
    {
        if ($course->status === CourseStatus::Suspended) {
            throw ValidationException::withMessages([
                'status' => 'This course has been suspended and cannot be published.',
            ]);
        }

        if (! $course->modules()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Add at least one module before publishing.',
            ]);
        }

        // Assigned directly: status is intentionally not mass-assignable.
        $course->status = CourseStatus::Published;
        $course->save();
    }
}

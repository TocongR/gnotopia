<?php

namespace App\Actions\Admin;

use App\Enums\CourseStatus;
use App\Models\Course;

class SetCourseSuspension
{
    public function handle(Course $course, bool $suspended): void
    {
        if ($suspended) {
            $course->status = CourseStatus::Suspended;
        } elseif ($course->status === CourseStatus::Suspended) {
            // A restored course comes back as a draft: the instructor decides when it goes live again.
            $course->status = CourseStatus::Draft;
        }

        $course->save();
    }
}

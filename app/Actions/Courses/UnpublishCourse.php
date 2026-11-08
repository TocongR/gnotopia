<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;

class UnpublishCourse
{
    public function handle(Course $course): void
    {
        if ($course->status === CourseStatus::Published) {
            $course->status = CourseStatus::Draft;
            $course->save();
        }
    }
}

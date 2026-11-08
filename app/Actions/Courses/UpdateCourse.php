<?php

namespace App\Actions\Courses;

use App\Models\Course;

class UpdateCourse
{
    public function handle(Course $course, array $data): Course
    {
        $course->fill($data)->save();

        return $course;
    }
}

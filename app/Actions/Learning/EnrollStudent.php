<?php

namespace App\Actions\Learning;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollStudent
{
    public function handle(User $student, Course $course): Enrollment
    {
        // createOrFirst survives a double click: the unique index rejects the
        // second insert, and the existing row is returned instead.
        return Enrollment::createOrFirst(
            ['user_id' => $student->id, 'course_id' => $course->id],
            ['enrolled_at' => now()],
        );
    }
}

<?php

namespace App\Actions\Learning;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

class CompleteLesson
{
    public function __construct(private CheckCourseCompletion $checkCompletion) {}

    public function handle(User $student, Lesson $lesson): void
    {
        LessonProgress::createOrFirst(
            ['user_id' => $student->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()],
        );

        $this->checkCompletion->handle($student, $lesson->module->course_id);
    }
}

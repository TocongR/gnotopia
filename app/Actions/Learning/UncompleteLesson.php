<?php

namespace App\Actions\Learning;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

class UncompleteLesson
{
    public function __construct(private CheckCourseCompletion $checkCompletion) {}

    public function handle(User $student, Lesson $lesson): void
    {
        LessonProgress::where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->delete();

        // The one place completion can be taken back.
        $this->checkCompletion->handle($student, $lesson->module->course_id, revoke: true);
    }
}

<?php

namespace App\Actions\Learning;

use App\Models\Enrollment;
use App\Models\User;
use App\Services\ProgressService;

class CheckCourseCompletion
{
    public function __construct(private ProgressService $progress) {}

    /**
     * Mark the student's enrollment as completed when every step is done.
     *
     * Completion is only ever granted here, never revoked, unless $revoke is true
     * (which only the explicit "mark as incomplete" action uses). That keeps a
     * completed course completed when an instructor adds content later.
     *
     * @return bool true when THIS call completed the course
     */
    public function handle(User $student, int $courseId, bool $revoke = false): bool
    {
        $enrollment = Enrollment::where('user_id', $student->id)
            ->where('course_id', $courseId)
            ->first();

        if (! $enrollment) {
            return false;
        }

        $summary = $this->progress->summaries($student, collect([$courseId]))[$courseId];

        // A course with nothing in it can never be "finished".
        $finished = $summary['total'] > 0 && $summary['done'] >= $summary['total'];

        if ($finished && $enrollment->completed_at === null) {
            $enrollment->completed_at = now();
            $enrollment->save();

            return true;
        }

        if (! $finished && $revoke && $enrollment->completed_at !== null) {
            $enrollment->completed_at = null;
            $enrollment->save();
        }

        return false;
    }
}

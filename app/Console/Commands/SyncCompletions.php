<?php

namespace App\Console\Commands;

use App\Actions\Learning\CheckCourseCompletion;
use App\Models\Enrollment;
use Illuminate\Console\Command;

class SyncCompletions extends Command
{
    protected $signature = 'completions:sync';

    protected $description = 'Mark enrollments as completed when the student has already finished every step';

    public function handle(CheckCourseCompletion $check): int
    {
        $marked = 0;

        Enrollment::query()
            ->whereNull('completed_at')
            ->with('user')
            ->chunkById(200, function ($enrollments) use ($check, &$marked) {
                foreach ($enrollments as $enrollment) {
                    if ($check->handle($enrollment->user, $enrollment->course_id)) {
                        $marked++;
                    }
                }
            });

        $this->info("Marked {$marked} enrollment(s) as completed.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class ProgressService
{
    /**
     * Lesson progress for several courses at once: two queries, however many courses.
     *
     * @param  Collection<int, int>  $courseIds
     * @return array<int, array{done: int, total: int, percent: int}>
     */
    public function summaries(User $user, Collection $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [];
        }

        $totals = Lesson::query()
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(*) as total')
            ->pluck('total', 'course_id');

        $done = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('lesson_progress.user_id', $user->id)
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(*) as done')
            ->pluck('done', 'course_id');

        return $courseIds->mapWithKeys(function ($id) use ($totals, $done) {
            $total = (int) ($totals[$id] ?? 0);
            $completed = min((int) ($done[$id] ?? 0), $total);

            return [$id => [
                'done' => $completed,
                'total' => $total,
                'percent' => $total > 0 ? (int) floor($completed / $total * 100) : 0,
            ]];
        })->all();
    }
}

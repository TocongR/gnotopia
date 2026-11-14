<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProgressService
{
    /**
     * Progress for several courses at once: a handful of queries, however many courses.
     * A "step" is a lesson (done when completed) or a quiz (done when passed).
     *
     * @param  Collection<int, int>  $courseIds
     * @return array<int, array{done: int, total: int, percent: int}>
     */
    public function summaries(User $user, Collection $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [];
        }

        $lessonTotals = Lesson::query()
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(*) as total')
            ->pluck('total', 'course_id');

        $quizTotals = $this->quizzesWithQuestions()
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(*) as total')
            ->pluck('total', 'course_id');

        $lessonsDone = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('lesson_progress.user_id', $user->id)
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(*) as done')
            ->pluck('done', 'course_id');

        $quizzesDone = $this->quizzesWithQuestions()
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.user_id', $user->id)
            ->where('quiz_attempts.passed', true)
            ->whereIn('modules.course_id', $courseIds)
            ->groupBy('modules.course_id')
            ->selectRaw('modules.course_id as course_id, count(distinct quizzes.id) as done')
            ->pluck('done', 'course_id');

        return $courseIds->mapWithKeys(function ($id) use ($lessonTotals, $quizTotals, $lessonsDone, $quizzesDone) {
            $total = (int) ($lessonTotals[$id] ?? 0) + (int) ($quizTotals[$id] ?? 0);
            $done = min((int) ($lessonsDone[$id] ?? 0) + (int) ($quizzesDone[$id] ?? 0), $total);

            return [$id => [
                'done' => $done,
                'total' => $total,
                'percent' => $total > 0 ? (int) floor($done / $total * 100) : 0,
            ]];
        })->all();
    }

    /** Quizzes a student can actually take: the ones that have at least one question. */
    private function quizzesWithQuestions(): Builder
    {
        return Quiz::query()
            ->join('modules', 'modules.id', '=', 'quizzes.module_id')
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('questions')
                ->whereColumn('questions.quiz_id', 'quizzes.id'));
    }
}

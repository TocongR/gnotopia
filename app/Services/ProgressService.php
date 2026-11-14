<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProgressService
{
    /**
     * One student's progress across several courses: a handful of queries, however many courses.
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

        return $courseIds->mapWithKeys(fn ($id) => [$id => $this->shape(
            (int) ($lessonsDone[$id] ?? 0) + (int) ($quizzesDone[$id] ?? 0),
            (int) ($lessonTotals[$id] ?? 0) + (int) ($quizTotals[$id] ?? 0),
        )])->all();
    }

    /**
     * Many students' progress in one course (for the instructor's student list).
     *
     * @param  Collection<int, int>  $userIds
     * @return array<int, array{done: int, total: int, percent: int}>
     */
    public function forCourse(Course $course, Collection $userIds): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        $total = Lesson::query()
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('modules.course_id', $course->id)
            ->count()
            + $this->quizzesWithQuestions()->where('modules.course_id', $course->id)->count();

        $lessonsDone = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('modules.course_id', $course->id)
            ->whereIn('lesson_progress.user_id', $userIds)
            ->groupBy('lesson_progress.user_id')
            ->selectRaw('lesson_progress.user_id as user_id, count(*) as done')
            ->pluck('done', 'user_id');

        $quizzesDone = $this->quizzesWithQuestions()
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.passed', true)
            ->where('modules.course_id', $course->id)
            ->whereIn('quiz_attempts.user_id', $userIds)
            ->groupBy('quiz_attempts.user_id')
            ->selectRaw('quiz_attempts.user_id as user_id, count(distinct quizzes.id) as done')
            ->pluck('done', 'user_id');

        return $userIds->mapWithKeys(fn ($id) => [$id => $this->shape(
            (int) ($lessonsDone[$id] ?? 0) + (int) ($quizzesDone[$id] ?? 0),
            $total,
        )])->all();
    }

    /** @return array{done: int, total: int, percent: int} */
    private function shape(int $done, int $total): array
    {
        $done = min($done, $total);

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) floor($done / $total * 100) : 0,
        ];
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

<?php

namespace App\Services;

use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LearningPath
{
    /** Load the course with its lessons and the quizzes that can be taken. */
    public function load(Course $course): Course
    {
        $course->load([
            'modules.lessons:id,module_id,title,type,position',
            'modules.quizzes' => fn ($query) => $query
                ->select('id', 'module_id', 'title', 'position')
                ->withCount('questions'),
        ]);

        // A quiz without questions cannot be taken, so it is not part of the path.
        $course->modules->each(fn (Module $module) => $module->setRelation(
            'quizzes',
            $module->quizzes->filter(fn (Quiz $quiz) => $quiz->questions_count > 0)->values(),
        ));

        return $course;
    }

    /**
     * Every step in course order. Requires load() first.
     *
     * @return Collection<int, array{key: string, type: string, id: int, title: string, kind: string, position: int}>
     */
    public function steps(Course $course): Collection
    {
        return $course->modules->flatMap(fn (Module $module) => $module->outline())->values();
    }

    /**
     * Keys of the steps this student has finished: "lesson-4", "quiz-2".
     *
     * @param  Collection<int, array>  $steps
     * @return array<int, string>
     */
    public function completedKeys(User $user, Collection $steps): array
    {
        $lessons = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $steps->where('type', 'lesson')->pluck('id'))
            ->pluck('lesson_id')
            ->map(fn ($id) => 'lesson-'.$id);

        $quizzes = QuizAttempt::where('user_id', $user->id)
            ->where('passed', true)
            ->whereIn('quiz_id', $steps->where('type', 'quiz')->pluck('id'))
            ->distinct()
            ->pluck('quiz_id')
            ->map(fn ($id) => 'quiz-'.$id);

        return $lessons->merge($quizzes)->all();
    }

    public function url(Course $course, array $step): string
    {
        return $step['type'] === 'quiz'
            ? route('student.learn.quiz', [$course->slug, $step['id']])
            : route('student.learn.lesson', [$course->slug, $step['id']]);
    }

    /** The sidebar tree. Requires load() first. */
    public function sidebar(Course $course, array $done): Collection
    {
        return $course->modules->map(fn (Module $module) => [
            'id' => $module->id,
            'title' => $module->title,
            'items' => $module->outline()->map(
                fn (array $step) => Arr::only($step, ['key', 'type', 'id', 'title', 'kind']) + [
                    'completed' => in_array($step['key'], $done, true),
                    'url' => $this->url($course, $step),
                ]
            )->values(),
        ])->values();
    }

    /** A previous or next link, or null at either end of the course. */
    public function neighbour(Course $course, ?array $step): ?array
    {
        return $step
            ? Arr::only($step, ['type', 'id', 'title']) + ['url' => $this->url($course, $step)]
            : null;
    }
}

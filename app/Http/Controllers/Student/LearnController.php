<?php

namespace App\Http\Controllers\Student;

use App\Enums\LessonType;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Services\ProgressService;
use App\Support\RichText;
use App\Support\VideoEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    /** Send the student to where they left off. */
    public function show(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('learn', $course);

        $course->load('modules.lessons:id,module_id,title,type,position');
        $lessons = $course->modules->flatMap(fn (Module $module) => $module->lessons)->values();

        if ($lessons->isEmpty()) {
            return redirect()
                ->route('student.dashboard')
                ->with('error', 'This course does not have any lessons yet.');
        }

        $completed = LessonProgress::where('user_id', $request->user()->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->pluck('lesson_id');

        $target = $lessons->first(fn (Lesson $lesson) => ! $completed->contains($lesson->id))
            ?? $lessons->first();

        // Keep a flash message (like "You are enrolled") alive through this extra redirect.
        $request->session()->reflash();

        return redirect()->route('student.learn.lesson', [$course->slug, $target->id]);
    }

    public function lesson(Request $request, Course $course, Lesson $lesson, ProgressService $progress): Response
    {
        Gate::authorize('learn', $course);

        // The lesson in the URL must belong to the course in the URL.
        abort_unless($lesson->module->course_id === $course->id, 404);

        $course->load([
            'modules.lessons:id,module_id,title,type,position',
            'modules.quizzes:id,module_id,title,position',
        ]);

        $user = $request->user();

        $lessonIds = $course->modules->flatMap(fn (Module $module) => $module->lessons->pluck('id'));

        $completedIds = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Every lesson in course order.
        $flat = $course->modules
            ->flatMap(fn (Module $module) => $module->outline()->where('type', 'lesson'))
            ->values();

        $position = $flat->search(fn (array $item) => $item['id'] === $lesson->id);
        abort_if($position === false, 404);

        return Inertia::render('Learn/Lesson', [
            'course' => $course->only('id', 'title', 'slug'),
            'modules' => $course->modules->map(fn (Module $module) => [
                'id' => $module->id,
                'title' => $module->title,
                'items' => $module->outline()->map(
                    fn (array $item) => Arr::only($item, ['key', 'type', 'id', 'title', 'kind']) + [
                        'completed' => $item['type'] === 'lesson' && in_array($item['id'], $completedIds, true),
                    ]
                )->values(),
            ])->values(),
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'kind' => $lesson->type->value,
                'embed_url' => $lesson->type === LessonType::Video
                    ? VideoEmbed::toEmbedUrl($lesson->video_url)
                    : null,
                // Sanitized on save too, but seeders and imports can bypass that, so clean on read as well.
                'body' => $lesson->type === LessonType::Article
                    ? RichText::clean($lesson->body)
                    : null,
                'completed' => in_array($lesson->id, $completedIds, true),
            ],
            'progress' => $progress->summaries($user, collect([$course->id]))[$course->id],
            'prev' => $this->neighbour($flat->get($position - 1)),
            'next' => $this->neighbour($flat->get($position + 1)),
        ]);
    }

    private function neighbour(?array $item): ?array
    {
        return $item ? Arr::only($item, ['id', 'title']) : null;
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Enums\LessonType;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LearningPath;
use App\Services\ProgressService;
use App\Support\RichText;
use App\Support\VideoEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    /** Send the student to where they left off. */
    public function show(Request $request, Course $course, LearningPath $path): RedirectResponse
    {
        Gate::authorize('learn', $course);

        $path->load($course);
        $steps = $path->steps($course);

        if ($steps->isEmpty()) {
            return redirect()
                ->route('student.dashboard')
                ->with('error', 'This course does not have any content yet.');
        }

        $done = $path->completedKeys($request->user(), $steps);

        $target = $steps->first(fn (array $step) => ! in_array($step['key'], $done, true))
            ?? $steps->first();

        // Keep a flash message (like "You are enrolled") alive through this extra redirect.
        $request->session()->reflash();

        return redirect($path->url($course, $target));
    }

    public function lesson(
        Request $request,
        Course $course,
        Lesson $lesson,
        LearningPath $path,
        ProgressService $progress,
    ): Response {
        Gate::authorize('learn', $course);

        // The lesson in the URL must belong to the course in the URL.
        abort_unless($lesson->module->course_id === $course->id, 404);

        $user = $request->user();

        $path->load($course);
        $steps = $path->steps($course);
        $done = $path->completedKeys($user, $steps);

        $position = $steps->search(fn (array $step) => $step['key'] === 'lesson-'.$lesson->id);
        abort_if($position === false, 404);

        return Inertia::render('Learn/Lesson', [
            'course' => $course->only('id', 'title', 'slug'),
            'modules' => $path->sidebar($course, $done),
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
                'completed' => in_array('lesson-'.$lesson->id, $done, true),
            ],
            'progress' => $progress->summaries($user, collect([$course->id]))[$course->id],
            'prev' => $path->neighbour($course, $steps->get($position - 1)),
            'next' => $path->neighbour($course, $steps->get($position + 1)),
        ]);
    }
}

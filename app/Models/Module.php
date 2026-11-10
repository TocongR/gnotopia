<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'position'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->orderBy('position');
    }

    public function nextItemPosition(): int
    {
        // Lessons and quizzes share one sequence inside a module.
        return max(
            (int) Lesson::where('module_id', $this->id)->max('position'),
            (int) Quiz::where('module_id', $this->id)->max('position'),
        ) + 1;
    }

    /**
     * Lessons and quizzes merged into one list, in position order.
     * Expects the `lessons` and `quizzes` relations to be loaded already.
     *
     * @return Collection<int, array{key: string, type: string, id: int, title: string, kind: string, position: int}>
     */
    public function outline(): Collection
    {
        return $this->lessons
            ->map(fn (Lesson $lesson) => [
                'key' => 'lesson-'.$lesson->id,
                'type' => 'lesson',
                'id' => $lesson->id,
                'title' => $lesson->title,
                'kind' => $lesson->type->value,
                'position' => $lesson->position,
            ])
            ->concat($this->quizzes->map(fn (Quiz $quiz) => [
                'key' => 'quiz-'.$quiz->id,
                'type' => 'quiz',
                'id' => $quiz->id,
                'title' => $quiz->title,
                'kind' => 'quiz',
                'position' => $quiz->position,
            ]))
            ->sortBy('position')
            ->values();
    }
}

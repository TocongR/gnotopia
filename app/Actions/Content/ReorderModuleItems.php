<?php

namespace App\Actions\Content;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderModuleItems
{
    /** @param  array<int, array{type: string, id: int|string}>  $items  Lessons and quizzes in the new order. */
    public function handle(Module $module, array $items): void
    {
        $expected = $module->lessons()->pluck('id')->map(fn ($id) => 'lesson:'.(int) $id)
            ->merge($module->quizzes()->pluck('id')->map(fn ($id) => 'quiz:'.(int) $id))
            ->sort()->values()->all();

        $given = collect($items)->map(fn ($item) => $item['type'].':'.(int) $item['id'])
            ->sort()->values()->all();

        if ($expected !== $given) {
            throw ValidationException::withMessages([
                'items' => 'The lesson list is out of date. Refresh the page and try again.',
            ]);
        }

        DB::transaction(function () use ($items) {
            foreach (array_values($items) as $index => $item) {
                $model = $item['type'] === 'lesson' ? Lesson::class : Quiz::class;
                $model::whereKey($item['id'])->update(['position' => $index + 1]);
            }
        });
    }
}

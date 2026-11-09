<?php

namespace App\Actions\Content;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderModules
{
    /** @param  array<int, int|string>  $ids  Module ids in the new order. */
    public function handle(Course $course, array $ids): void
    {
        $expected = $course->modules()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $given = collect($ids)->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($expected !== $given) {
            throw ValidationException::withMessages([
                'ids' => 'The module list is out of date. Refresh the page and try again.',
            ]);
        }

        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $index => $id) {
                Module::whereKey($id)->update(['position' => $index + 1]);
            }
        });
    }
}

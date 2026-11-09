<?php

namespace App\Actions\Content;

use App\Models\Module;
use Illuminate\Validation\ValidationException;

class DeleteModule
{
    public function handle(Module $module): void
    {
        $course = $module->course;

        if ($course->isPublished() && $course->modules()->count() <= 1) {
            throw ValidationException::withMessages([
                'module' => 'A published course needs at least one module. Unpublish the course first.',
            ]);
        }

        $module->delete();
    }
}

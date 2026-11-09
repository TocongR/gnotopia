<?php

namespace App\Actions\Content;

use App\Models\Course;
use App\Models\Module;

class AddModule
{
    public function handle(Course $course, string $title): Module
    {
        $module = new Module(['title' => $title]);
        $module->course_id = $course->id;
        $module->position = ((int) Module::where('course_id', $course->id)->max('position')) + 1;
        $module->save();

        return $module;
    }
}

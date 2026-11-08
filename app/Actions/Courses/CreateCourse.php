<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class CreateCourse
{
    public function handle(User $instructor, array $data): Course
    {
        $course = new Course($data);
        $course->slug = $this->uniqueSlug($data['title']);
        $course->user_id = $instructor->id;
        $course->status = CourseStatus::Draft;
        $course->save();

        return $course;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $suffix = 2;

        while (Course::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}

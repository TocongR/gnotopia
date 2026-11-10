<?php

namespace App\Support;

use App\Models\Course;
use Illuminate\Support\Str;

class CourseCard
{
    /**
     * Public-safe summary of a course.
     * Expects `category`, `instructor` and `modules_count` to be loaded already.
     */
    public static function make(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'excerpt' => Str::limit(trim(strip_tags((string) $course->description)), 140),
            'price_cents' => $course->price_cents,
            'category' => $course->category->name,
            'instructor' => $course->instructor->name,
            'modules_count' => $course->modules_count,
        ];
    }
}

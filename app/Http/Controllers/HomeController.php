<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Support\CourseCard;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $courses = Course::published()
            ->with(['category:id,name,slug', 'instructor:id,name'])
            ->withCount('modules')
            ->latest()
            ->orderByDesc('id')
            ->take(6)
            ->get()
            ->map(fn (Course $course) => CourseCard::make($course));

        $categories = Category::whereHas('courses', fn ($query) => $query->published())
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Home', [
            'courses' => $courses,
            'categories' => $categories,
        ]);
    }
}

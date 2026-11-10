<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogIndexRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Module;
use App\Support\CourseCard;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(CatalogIndexRequest $request): Response
    {
        $term = $request->validated('q');
        $categorySlug = $request->validated('category');

        $courses = Course::published()
            ->search($term)
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $categorySlug)
            ))
            ->with(['category:id,name,slug', 'instructor:id,name'])
            ->withCount('modules')
            ->latest()
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Course $course) => CourseCard::make($course));

        return Inertia::render('Catalog/Index', [
            'courses' => $courses,
            'categories' => Category::whereHas('courses', fn ($query) => $query->published())
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'filters' => [
                'q' => $term ?? '',
                'category' => $categorySlug ?? '',
            ],
        ]);
    }

    public function show(Course $course): Response
    {
        // Drafts and suspended courses do not exist as far as the public is concerned.
        abort_unless($course->isPublished(), 404);

        // Column lists keep lesson bodies and video URLs out of memory entirely.
        $course->load([
            'category:id,name,slug',
            'instructor:id,name',
            'modules.lessons:id,module_id,title,type,position',
            'modules.quizzes:id,module_id,title,position',
        ]);

        return Inertia::render('Catalog/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'price_cents' => $course->price_cents,
                'updated_at' => $course->updated_at->toFormattedDateString(),
                'category' => $course->category->only('name', 'slug'),
                'instructor' => $course->instructor->only('name'),
                'lessons_count' => $course->modules->sum(fn (Module $module) => $module->lessons->count()),
                'quizzes_count' => $course->modules->sum(fn (Module $module) => $module->quizzes->count()),
                'modules' => $course->modules->map(fn (Module $module) => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'items' => $module->outline()
                        ->map(fn (array $item) => Arr::only($item, ['key', 'type', 'title', 'kind']))
                        ->values(),
                ])->values(),
            ],
        ]);
    }
}

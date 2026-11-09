<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Content\ReorderModules;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\ReorderModulesRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;

class ReorderModulesController extends Controller
{
    public function __invoke(ReorderModulesRequest $request, Course $course, ReorderModules $reorderModules): RedirectResponse
    {
        $reorderModules->handle($course, $request->validated('ids'));

        return back();
    }
}

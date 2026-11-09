<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Content\AddModule;
use App\Actions\Content\DeleteModule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\SaveModuleRequest;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function store(SaveModuleRequest $request, Course $course, AddModule $addModule): RedirectResponse
    {
        $addModule->handle($course, $request->validated('title'));

        return back()->with('success', 'Module added.');
    }

    public function update(SaveModuleRequest $request, Module $module): RedirectResponse
    {
        $module->update($request->validated());

        return back()->with('success', 'Module renamed.');
    }

    public function destroy(Module $module, DeleteModule $deleteModule): RedirectResponse
    {
        Gate::authorize('update', $module->course);

        $deleteModule->handle($module);

        return back()->with('success', 'Module deleted.');
    }
}

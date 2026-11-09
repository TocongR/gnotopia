<?php

namespace App\Http\Controllers\Instructor;

use App\Actions\Content\ReorderModuleItems;
use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\ReorderModuleItemsRequest;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;

class ReorderModuleItemsController extends Controller
{
    public function __invoke(ReorderModuleItemsRequest $request, Module $module, ReorderModuleItems $reorderItems): RedirectResponse
    {
        $reorderItems->handle($module, $request->validated('items'));

        return back();
    }
}

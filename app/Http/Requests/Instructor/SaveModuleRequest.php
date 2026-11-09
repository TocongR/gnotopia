<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class SaveModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Create has {course} in the route, update has {module}.
        $course = $this->route('course') ?? $this->route('module')->course;

        return $this->user()->can('update', $course);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}

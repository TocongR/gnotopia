<?php

namespace App\Http\Requests\Admin;

use App\Enums\CourseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the admin route group already requires the admin role
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(CourseStatus::class)],
        ];
    }
}

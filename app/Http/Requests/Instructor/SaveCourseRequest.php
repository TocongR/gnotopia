<?php

namespace App\Http\Requests\Instructor;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class SaveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            ? $this->user()->can('update', $course)
            : $this->user()->can('create', Course::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    /** Validated data in the shape the database wants (price in pesos -> centavos). */
    public function courseData(): array
    {
        $data = $this->safe()->except('price');
        $data['price_cents'] = (int) round($this->validated('price') * 100);

        return $data;
    }
}

<?php

namespace App\Http\Requests\Instructor;

use App\Enums\LessonType;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Create has {module} in the route, update has {lesson}.
        $module = $this->route('module') ?? $this->route('lesson')->module;

        return $this->user()->can('update', $module->course);
    }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(LessonType::class)],
            'video_url' => [
                Rule::requiredIf($type === LessonType::Video->value),
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && VideoEmbed::toEmbedUrl($value) === null) {
                        $fail('Use a valid YouTube or Vimeo link.');
                    }
                },
            ],
            'body' => [
                Rule::requiredIf($type === LessonType::Article->value),
                'nullable',
                'string',
                'max:200000',
            ],
        ];
    }
}

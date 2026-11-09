<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Create has {module} in the route, update has {quiz}.
        $module = $this->route('module') ?? $this->route('quiz')->module;

        return $this->user()->can('update', $module->course);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'pass_percent' => ['required', 'integer', 'between:1,100'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.body' => ['required', 'string', 'max:1000'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.options.*.id' => ['nullable', 'integer'],
            'questions.*.options.*.body' => ['required', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    /** Every question needs exactly one correct option. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $questions = $this->input('questions');

                if (! is_array($questions)) {
                    return;
                }

                foreach ($questions as $index => $question) {
                    $options = is_array($question) ? ($question['options'] ?? null) : null;

                    // Too few options is already reported by the rules above.
                    if (! is_array($options) || count($options) < 2) {
                        continue;
                    }

                    $correct = collect($options)
                        ->filter(fn ($option) => is_array($option)
                            && filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))
                        ->count();

                    if ($correct !== 1) {
                        $validator->errors()->add(
                            "questions.$index.options",
                            'Mark exactly one option as the correct answer.'
                        );
                    }
                }
            },
        ];
    }
}

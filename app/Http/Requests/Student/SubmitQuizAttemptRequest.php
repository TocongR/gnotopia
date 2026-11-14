<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('learn', $this->route('course'));
    }

    /** `answers` maps question id => chosen option id. There is deliberately no score field. */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return ['answers.required' => 'Answer every question before submitting.'];
    }
}

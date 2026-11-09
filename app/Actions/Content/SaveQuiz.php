<?php

namespace App\Actions\Content;

use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveQuiz
{
    public function handle(Module $module, array $data, ?Quiz $quiz = null): Quiz
    {
        return DB::transaction(function () use ($module, $data, $quiz) {
            $attributes = [
                'title' => $data['title'],
                'pass_percent' => $data['pass_percent'],
            ];

            if ($quiz) {
                $quiz->fill($attributes)->save();
            } else {
                $quiz = new Quiz($attributes);
                $quiz->module_id = $module->id;
                $quiz->position = $module->nextItemPosition();
                $quiz->save();
            }

            $this->syncQuestions($quiz, $data['questions']);

            return $quiz;
        });
    }

    private function syncQuestions(Quiz $quiz, array $questions): void
    {
        $existing = $quiz->questions()->get()->keyBy('id');
        $kept = [];

        foreach (array_values($questions) as $index => $data) {
            $attributes = ['body' => $data['body'], 'position' => $index + 1];
            $id = $data['id'] ?? null;

            if ($id !== null) {
                $question = $existing->get((int) $id) ?? throw ValidationException::withMessages([
                    'questions' => 'A question no longer belongs to this quiz. Refresh the page and try again.',
                ]);

                $question->fill($attributes)->save();
            } else {
                $question = new Question($attributes);
                $question->quiz_id = $quiz->id;
                $question->save();
            }

            $kept[] = $question->id;

            $this->syncOptions($question, $data['options']);
        }

        // Anything the instructor removed from the form is deleted.
        $quiz->questions()->whereNotIn('id', $kept)->delete();
    }

    private function syncOptions(Question $question, array $options): void
    {
        $existing = $question->options()->get()->keyBy('id');
        $kept = [];

        foreach ($options as $data) {
            $attributes = ['body' => $data['body'], 'is_correct' => (bool) $data['is_correct']];
            $id = $data['id'] ?? null;

            if ($id !== null) {
                $option = $existing->get((int) $id) ?? throw ValidationException::withMessages([
                    'questions' => 'An answer option no longer belongs to its question. Refresh the page and try again.',
                ]);

                $option->fill($attributes)->save();
            } else {
                $option = new Option($attributes);
                $option->question_id = $question->id;
                $option->save();
            }

            $kept[] = $option->id;
        }

        $question->options()->whereNotIn('id', $kept)->delete();
    }
}

<?php

namespace App\Actions\Learning;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitQuizAttempt
{
    /**
     * @param  array<int|string, int|string>  $answers  question id => chosen option id
     */
    public function handle(User $student, Quiz $quiz, array $answers): QuizAttempt
    {
        $questions = $quiz->questions()->with('options')->get();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages(['answers' => 'This quiz has no questions yet.']);
        }

        $review = [];
        $correct = 0;

        foreach ($questions as $question) {
            $chosenId = $answers[$question->id] ?? null;

            // Looking the option up inside THIS question's options means an option
            // belonging to any other question (or quiz) can never be accepted.
            $chosen = $chosenId === null
                ? null
                : $question->options->firstWhere('id', (int) $chosenId);

            if ($chosen === null) {
                throw ValidationException::withMessages(['answers' => 'Answer every question before submitting.']);
            }

            $right = $question->options->firstWhere('is_correct', true);
            $isCorrect = $chosen->is_correct;
            $correct += $isCorrect ? 1 : 0;

            // A snapshot: later edits to the quiz must not change what the student sees here.
            $review[] = [
                'question_id' => $question->id,
                'question' => $question->body,
                'selected_id' => $chosen->id,
                'selected' => $chosen->body,
                'correct_id' => $right?->id,
                'correct' => $right?->body,
                'is_correct' => $isCorrect,
            ];
        }

        $total = $questions->count();

        return QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            // Integer math only: floor(correct / total * 100), and the pass check is
            // equivalent to score >= pass mark, without any rounding surprises.
            'score_percent' => intdiv($correct * 100, $total),
            'passed' => $correct * 100 >= (int) $quiz->pass_percent * $total,
            'answers' => $review,
        ]);
    }
}

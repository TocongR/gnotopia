<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    // Only ever filled by the codebase (SubmitQuizAttempt), never from request input.
    protected $fillable = ['user_id', 'quiz_id', 'score_percent', 'passed', 'answers'];

    protected function casts(): array
    {
        return [
            'score_percent' => 'integer',
            'passed' => 'boolean',
            'answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}

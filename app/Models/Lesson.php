<?php

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'video_url', 'body', 'position'];

    protected function casts(): array
    {
        return ['type' => LessonType::class];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}

<?php

namespace App\Models;

use App\Enums\CourseStatus;
use App\Support\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'image_path',
        'price_cents',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'price_cents' => 'integer',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = Like::contains($term);

        // lower() on both sides makes the match case-insensitive on every database.
        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw("lower(courses.title) like lower(?) escape '!'", [$like])
                ->orWhereRaw("lower(courses.description) like lower(?) escape '!'", [$like]);
        });
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function isFree(): bool
    {
        return $this->price_cents === 0;
    }
}

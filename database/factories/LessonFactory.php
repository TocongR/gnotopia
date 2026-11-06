<?php

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lesson> */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => fake()->sentence(4),
            'type' => LessonType::Article,
            'video_url' => null,
            'body' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'position' => 0,
        ];
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'type' => LessonType::Video,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'body' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Course> */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'user_id' => User::factory()->instructor(),
            'category_id' => Category::factory(),
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(2, true),
            'image_path' => null,
            'status' => CourseStatus::Draft,
            'price_cents' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Published]);
    }
}

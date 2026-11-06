<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Quiz> */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => fake()->sentence(3).' Quiz',
            'pass_percent' => 70,
            'position' => 0,
        ];
    }
}

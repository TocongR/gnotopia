<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCourseSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('email', 'instructor@gnotopia.test')->first()
            ?? User::factory()->instructor()->create([
                'name' => 'Demo Instructor',
                'email' => 'instructor@gnotopia.test',
            ]);

        $categories = Category::all();

        foreach (range(1, 5) as $i) {
            $course = Course::factory()
                ->for($instructor, 'instructor')
                ->for($categories->random())
                ->create([
                    // The last one stays a draft so we can see both states.
                    'status' => $i < 5 ? CourseStatus::Published : CourseStatus::Draft,
                ]);

            foreach (range(1, 3) as $m) {
                $module = Module::factory()->for($course)->create(['position' => $m]);

                // Lessons and the quiz share one position sequence: 1, 2, 3 = lessons, 4 = quiz.
                Lesson::factory()->for($module)->create(['position' => 1]);
                Lesson::factory()->video()->for($module)->create(['position' => 2]);
                Lesson::factory()->for($module)->create(['position' => 3]);

                $quiz = Quiz::factory()->for($module)->create(['position' => 4]);

                foreach (range(1, 3) as $q) {
                    $question = Question::factory()->for($quiz)->create(['position' => $q]);

                    Option::factory()->correct()->for($question)->create();
                    Option::factory()->count(3)->for($question)->create();
                }
            }
        }
    }
}

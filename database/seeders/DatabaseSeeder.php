<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
        ]);

        // Fake data only on in local machine, never in production.
        if (app()->environment('local')) {
            $this->call(DemoCourseSeeder::class);
        }
    }
}

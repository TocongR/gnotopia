<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(Role::Instructor);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    public function publish(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    private function owns(User $user, Course $course): bool
    {
        return $user->hasRole(Role::Instructor)
            && (int) $course->user_id === (int) $user->id;
    }
}

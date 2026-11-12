<?php

namespace App\Policies;

use App\Enums\CourseStatus;
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

    public function enroll(User $user, Course $course): bool
    {
        return $user->hasRole(Role::Student)
            && $course->isPublished()
            && $course->isFree()
            && ! $user->isEnrolledIn($course);
    }

    public function learn(User $user, Course $course): bool
    {
        return $user->hasRole(Role::Student)
            && $course->status !== CourseStatus::Suspended
            && $user->isEnrolledIn($course);
    }

    private function owns(User $user, Course $course): bool
    {
        return $user->hasRole(Role::Instructor)
            && (int) $course->user_id === (int) $user->id;
    }
}

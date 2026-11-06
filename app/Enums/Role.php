<?php

namespace App\Enums;

enum Role: string
{
    case Student = 'student';
    case Instructor = 'instructor';
    case Admin = 'admin';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public static function selfAssignable(): array
    {
        return [self::Student, self::Instructor];
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Student => 'student.dashboard',
            self::Instructor => 'instructor.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}

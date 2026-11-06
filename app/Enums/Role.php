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

    /** Roles a person may pick for themselves when registering. */
    public static function selfAssignable(): array
    {
        return [self::Student, self::Instructor];
    }
}

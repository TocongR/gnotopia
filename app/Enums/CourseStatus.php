<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Suspended = 'suspended';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

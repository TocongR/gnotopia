<?php

namespace App\Enums;

enum LessonType: string
{
    case Video = 'video';
    case Article = 'article';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
